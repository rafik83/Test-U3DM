<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Prospect;
use AppBundle\Entity\Tag;
use AppBundle\Event\ProspectEvent;
use AppBundle\Event\ProspectEvents;
use AppBundle\Form\ProspectType;
use AppBundle\Service\Mailer;
use AppBundle\Service\SendinBlue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProspectListener implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SendinBlue
     */
    private $sendinBlue;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;


    /**
     * ProspectListener constructor
     *
     * @param ObjectManager $entityManager
     * @param TranslatorInterface $translator
     * @param SendinBlue $sendinBlue
     * @param Mailer $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(ObjectManager $entityManager, TranslatorInterface $translator, SendinBlue $sendinBlue, Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->sendinBlue = $sendinBlue;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * Implements the interface method.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT           => 'handleProspectForm',
            ProspectEvents::PRE_PERSIST  => 'generateToken',
            ProspectEvents::POST_PERSIST => 'notifyAdmin'
        );
    }

    /**
     * Handle Prospect form submission and check for errors depending on Prospect type (Maker or Customer)
     *
     * @param FormEvent $event
     */
    public function handleProspectForm(FormEvent $event)
    {
        /** @var Prospect $prospect */
        $prospect = $event->getData();
        $form = $event->getForm();

        if ($prospect->isMaker()) {

            // make sure at least one Maker type is set
            if (!$prospect->isPrinter() && !$prospect->isDesigner()) {

                $form->addError(new FormError($this->translator->trans('prospect.maker.choose_type', array(), 'validators')));

            } else {

                // remove all customer related data
                $prospect->setCustomerType(null);
                foreach ($prospect->getDomains() as $domain) {
                    $prospect->removeDomain($domain);
                }

                // setup the technology tags type
                foreach ($prospect->getTechnologies() as $tag) {
                    if (Tag::TYPE_UNKNOWN == $tag->getType()) {
                        $tag->setType(Tag::TYPE_TECHNOLOGY);
                    }
                }
            }

        } else {

            // remove all maker related data
            $prospect->setPrinter(false);
            $prospect->setDesigner(false);
            foreach ($prospect->getTechnologies() as $technology) {
                $prospect->removeTechnology($technology);
            }

            // remove the company if the Prospect is not a company
            if (Prospect::CUSTOMER_TYPE_COMPANY !== $prospect->getCustomerType()) {
                $prospect->setCompany(null);
            }

            // setup the domain tags type
            foreach ($prospect->getDomains() as $tag) {
                if (Tag::TYPE_UNKNOWN == $tag->getType()) {
                    $tag->setType(Tag::TYPE_DOMAIN);
                }
            }
        }
    }

    /**
     * Check if the Prospect should subscribe to the newsletter, and perform subscription if needed.
     *
     * @param ProspectEvent $event
     */
    public function subscribeToNewsletter(ProspectEvent $event)
    {
        $prospect = $event->getProspect();
        if ($prospect->isNewsletter()) {
            $this->sendinBlue->subscribeToNewsletter($prospect->getEmail());
        }
    }

    /**
     * Notify the Prospect that he has been created.
     *
     * @param ProspectEvent $event
     */
    public function notifyProspect(ProspectEvent $event)
    {
        $prospect = $event->getProspect();
        $this->sendinBlue->sendTransactional(SendinBlue::TEMPLATE_ID_PROSPECT, $prospect->getEmail(), $prospect->getFullname());
    }

    /**
     * Notify the admin that a new prospect has been created.
     *
     * @param ProspectEvent $event
     */
    public function notifyAdmin(ProspectEvent $event)
    {
        // get prospect type label
        $prospectType = 'prospect.type.customer';
        if ($event->getProspect()->isMaker()) {
            $prospectType = 'prospect.type.maker';
        }

        // subject
        $subject = $this->translator->trans('email.notification.admin.prospect.create.subject', array('%type%' =>  $this->translator->trans($prospectType)));

        // body
        $body = $this->translator->trans('email.notification.admin.prospect.create.body', array('%type%' =>  $this->translator->trans($prospectType), '%name%' => $event->getProspect()->getFullname()));

        // create and send the notification message to the configured admin address
        $message = $this->mailer->createAdminNotificationMessage($subject, $body);
        $this->mailer->send($message);
    }

    /**
     * Generate a token for that prospect.
     *
     * @param ProspectEvent $event
     */
    public function generateToken(ProspectEvent $event)
    {
        $prospect = $event->getProspect();
        $prospect->setToken($this->tokenGenerator->generateToken());
    }
}