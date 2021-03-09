<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Event\UserEvent;
use AppBundle\Event\UserEvents;
use AppBundle\Service\Mailer;
use AppBundle\Service\SendinBlue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Translation\TranslatorInterface;

class UserListener implements EventSubscriberInterface
{
    private $objectManager;

    private $tokenGenerator;

    private $router;

    private $translator;

    private $mailer;

    private $sendinBlue;

    private $session;

    public function __construct(ObjectManager $objectManager, TokenGeneratorInterface $tokenGenerator, RouterInterface $router, TranslatorInterface $translator, Mailer $mailer, SendinBlue $sendinBlue, SessionInterface $session)
    {
        $this->objectManager = $objectManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->sendinBlue = $sendinBlue;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::REGISTER_PRE_PERSIST  => 'generateEnableToken',
            UserEvents::REGISTER_POST_PERSIST => array(array('sendEnableEmail', 10), array('subscribeToNewsletter', 0)),
            UserEvents::ENABLE_POST_UPDATE    => 'notifyAdmin',
            UserEvents::PROFILE_POST_UPDATE   => 'subscribeToNewsletter',
            UserEvents::FORGOT_PASSWORD_PRE_UPDATE  => 'setResetToken',
            UserEvents::FORGOT_PASSWORD_POST_UPDATE => 'sendResetPasswordEmail',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'resendEnableEmail',
            SecurityEvents::INTERACTIVE_LOGIN => 'setLatestLoginAt',
            FormEvents::SUBMIT                => 'handleProfileForm'
        );
    }

    public function generateEnableToken(UserEvent $event)
    {
        $user = $event->getUser();
        $enableToken = $this->tokenGenerator->generateToken();
        $user->setEnableToken($enableToken);
    }

    public function resendEnableEmail(AuthenticationFailureEvent $event)
    {
        $authToken = $event->getAuthenticationToken();
        $email = $authToken->getUsername();
        /** @var User $user */
        $user = $this->objectManager->getRepository('AppBundle:User')->findOneByEmail($email);
        if (null !== $user && !$user->isEnabled() && null !== $user->getEnableToken()) {
            $this->session->getFlashBag()->add('warning', 'Ce compte n\'a pas encore été activé : l\'e-mail d\'activation vient de vous être à nouveau envoyé.');
            $this->sendEnableEmail(new UserEvent($user));
        }
    }

    public function sendEnableEmail(UserEvent $event)
    {
        $user = $event->getUser();
        $enableUrl = $this->router->generate('user_register_enable', array('token' => $user->getEnableToken()), $this->router::ABSOLUTE_URL);

        // send customer e-mail
        $params = array(
            'customerName' => $user->getFullname(),
            'enableUrl'    => $enableUrl
        );

        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_ENABLE_ACCOUNT,
            $user->getEmail(),
            $user->getFullname(),
            $params
        );

        // backup: simple e-mail
        //$message = $this->mailer->createEnableMessage($user->getEmail(), $user->getFullname(), $enableUrl);
        //$this->mailer->send($message);
    }

    public function setLatestLoginAt(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof User) {
            $user->setPreviousLoginAt($user->getLatestLoginAt());
            $user->setLatestLoginAt(new \DateTime());
            $this->objectManager->flush();
        }
    }

    /**
     * Notify the admin that a new user has been enabled.
     *
     * @param UserEvent $event
     */
    public function notifyAdmin(UserEvent $event)
    {
        // subject
        $subject = $this->translator->trans('email.notification.admin.user.enable.subject');

        // body
        $body = $this->translator->trans('email.notification.admin.user.enable.body', array('%name%' => $event->getUser()->getFullname()));

        // create and send the notification message to the configured admin address
        $message = $this->mailer->createAdminNotificationMessage($subject, $body);
        $this->mailer->send($message);
    }

    /**
     * Check if the User should subscribe to the newsletter, and perform subscription if needed.
     *
     * @param UserEvent $event
     */
    public function subscribeToNewsletter(UserEvent $event)
    {
        $user = $event->getUser();
        if ($user->isNewsletter()) {
            $this->sendinBlue->subscribeToNewsletter($user->getEmail());
        } else {
            $this->sendinBlue->subscribeToNewsletter($user->getEmail(), false);
        }
    }

    /**
     * Set the user reset token
     *
     * @param UserEvent $event
     */
    public function setResetToken(UserEvent $event)
    {
        $user = $event->getUser();
        $token = $this->tokenGenerator->generateToken();
        $user->setResetToken($token);
    }

    /**
     * Send the reset password e-mail to the user
     *
     * @param UserEvent $event
     */
    public function sendResetPasswordEmail(UserEvent $event)
    {
        $user = $event->getUser();
        $resetUrl = $this->router->generate('user_reset_password', array('token' => $user->getResetToken()), $this->router::ABSOLUTE_URL);

        $params = array(
            'name'               => $user->getFullname(),
            'reset_password_url' => $resetUrl
        );

        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_RESET_PASSWORD,
            $user->getEmail(),
            $user->getFullname(),
            $params
        );
    }

    /**
     * Handle the user profile form
     *
     * @param FormEvent $event
     */
    public function handleProfileForm(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getData();

        $form = $event->getForm();

        // handle addresses
        if ($user->isSameAddress()) {
            // set default billing address as the same as default shipping address
            if (null === $user->getDefaultBillingAddress()) {
                // billing address did not exist, clone the shipping address
                $user->setDefaultBillingAddress(clone $user->getDefaultShippingAddress());
            } else {
                // billing address already existed, make sure to recopy the shipping address
                $shippingAddress = $user->getDefaultShippingAddress();
                $billingAddress = $user->getDefaultBillingAddress();
                $billingAddress->setFirstname($shippingAddress->getFirstname());
                $billingAddress->setLastname($shippingAddress->getLastname());
                $billingAddress->setCompany($shippingAddress->getCompany());
                $billingAddress->setStreet1($shippingAddress->getStreet1());
                $billingAddress->setStreet2($shippingAddress->getStreet2());
                $billingAddress->setZipcode($shippingAddress->getZipcode());
                $billingAddress->setCity($shippingAddress->getCity());
                $billingAddress->setCountry($shippingAddress->getCountry());
                $billingAddress->setTelephone($shippingAddress->getTelephone());
            }
        } else {
            // billing address is not the same as shipping address, make sure the required field are filled
            $billingAddress = $user->getDefaultBillingAddress();
            if (null === $billingAddress->getFirstname()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.firstname')), 'validators')));
            }
            if (null === $billingAddress->getLastname()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.lastname')), 'validators')));
            }
            if (null === $billingAddress->getStreet1()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.street1')), 'validators')));
            }
            if (null === $billingAddress->getZipcode()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.zip_code')), 'validators')));
            }
            if (null === $billingAddress->getCity()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.city')), 'validators')));
            }
            if (null === $billingAddress->getCountry()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.country')), 'validators')));
            }
            if (null === $billingAddress->getTelephone()) {
                $form->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.telephone')), 'validators')));
            }
        }

        // nullify company fields if user is an individual
        if (User::TYPE_INDIVIDUAL === $user->getType()) {
            $user->setCompany(null);
            $user->setCompanyType(null);
            $user->getDefaultShippingAddress()->setCompany(null);
            $user->getDefaultBillingAddress()->setCompany(null);
        }
    }
}