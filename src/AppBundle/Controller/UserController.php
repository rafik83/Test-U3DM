<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\Maker;
use AppBundle\Entity\Message;
use AppBundle\Entity\Order;
use AppBundle\Entity\Project;
use AppBundle\Entity\Prospect;
use AppBundle\Entity\Quotation;
use AppBundle\Entity\Setting;
use AppBundle\Entity\User;
use AppBundle\Entity\Model;
use AppBundle\Entity\CategoryModel;
use AppBundle\Entity\Signal;
use AppBundle\Event\UserEvent;
use AppBundle\Event\UserEvents;
use AppBundle\Form\MakerBankSetupType;
use AppBundle\Form\MakerDetailsType;
use AppBundle\Form\MakerType;
use AppBundle\Form\UserProfileType;
use AppBundle\Form\UserRegistrationType;
use AppBundle\Form\UserResetPasswordType;
use AppBundle\Form\UserModifyPasswordType;
use AppBundle\Form\MakerAddModelType;
use AppBundle\Form\CategoryModelType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use AppBundle\Service\StripeManager;
use Stripe\Account;
use Stripe\Person;
use Stripe\BankAccount;

use Psr\Log\LoggerInterface;

/**
 * @Route("/%app.user_directory%")
 */
class UserController extends Controller
{

    private $logger;

    public function __construct(  LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/login", name="user_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        // @see https://symfony.com/doc/current/security/form_login_setup.html
        // @see https://symfony.com/doc/current/security/form_login.html
        // TODO @see https://symfony.com/doc/current/security/remember_me.html

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front/user/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/inscription/{prospectToken}", name="user_register", defaults={"prospectToken" = null})
     *
     * @param string $prospectToken
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function registerAction($prospectToken, Request $request, UserPasswordEncoderInterface $passwordEncoder, EventDispatcherInterface $eventDispatcher, ObjectManager $entityManager)
    {
        // @see http://symfony.com/doc/current/doctrine/registration_form.html


        $user = new User();

        if (null !== $prospectToken) {
            /** @var Prospect $prospect */
            $prospect = $entityManager->getRepository('AppBundle:Prospect')->findOneByToken($prospectToken);
            if (null === $prospect) {
                return $this->redirectToRoute('user_register');
            } else {
                $user->setEmail($prospect->getEmail());
                $user->setFirstname($prospect->getFirstname());
                $user->setLastname($prospect->getLastname());
                if (null !== $prospect->getCustomerType()) {
                    $user->setType($prospect->getCustomerType());
                }
            }
        }

        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // encode the password
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // update default billing address and set default shipping address to the same address
            $user->getDefaultBillingAddress()->setFirstname($user->getFirstname());
            $user->getDefaultBillingAddress()->setLastname($user->getLastname());
            $user->setDefaultShippingAddress(clone $user->getDefaultBillingAddress());
            $user->setSameAddress(true);

            // send an event
            $eventDispatcher->dispatch(UserEvents::REGISTER_PRE_PERSIST, new UserEvent($user));

            // persist
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // send an event
            $eventDispatcher->dispatch(UserEvents::REGISTER_POST_PERSIST, new UserEvent($user));

            // redirect to a new specific route, to avoid a page refresh and thus a double form submission with errors
            return $this->redirectToRoute('user_register_confirm');
        }

        return $this->render('front/user/register.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/merci", name="user_register_confirm")
     *
     * @return Response
     */
    public function registerConfirmAction()
    {
        return $this->render('front/user/register.html.twig');
    }

    /**
     * @Route("/activation/{token}", name="user_register_enable")
     * @Method({"GET"})
     *
     * @param string $token
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function enableAction($token, Request $request, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        /** @var User|null $user */
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneByEnableToken($token);
        if (null === $user) {
            return $this->redirectToRoute('user_login');
        }

        // update the user
        $user->setEnabled(true);
        $user->setEnabledAt(new \DateTime());
        $user->setEnableToken(null);
        $this->getDoctrine()->getManager()->flush();

        // implicit login
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $tokenStorage->setToken($token);

        // dispatch an event just like when the user logs in via the form
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

        // send an event
        $eventDispatcher->dispatch(UserEvents::ENABLE_POST_UPDATE, new UserEvent($user));

        // redirect to the profile page
        $this->addFlash('success', 'user.flash.enabled');
        return $this->redirectToRoute('user_profile');
    }

    /**
     * @Route("/mot-de-passe-oublie", name="user_forgot_password")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function forgotPasswordAction(Request $request, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $email = $request->get('email');

        if (null !== $email) {

            // look for the user
            $user = $entityManager->getRepository('AppBundle:User')->findOneByEmail($email);

            // proceed if an user was found
            if (null !== $user) {

                // dispatch an event
                $eventDispatcher->dispatch(UserEvents::FORGOT_PASSWORD_PRE_UPDATE, new UserEvent($user));

                // flush
                $entityManager->flush();

                // dispatch an event
                $eventDispatcher->dispatch(UserEvents::FORGOT_PASSWORD_POST_UPDATE, new UserEvent($user));
            }

            // redirect to a new specific route, to avoid a page refresh and thus a double form submission
            return $this->redirectToRoute('user_forgot_password_confirm');

        }

        return $this->render('front/user/forgot_password.html.twig', array('form' => true));
    }

    /**
     * @Route("/mot-de-passe-oublie/confirmation", name="user_forgot_password_confirm")
     *
     * @return Response
     */
    public function forgotPasswordConfirmAction()
    {
        return $this->render('front/user/forgot_password.html.twig');
    }

    /**
     * @Route("/mot-de-passe-oublie/{token}", name="user_reset_password")
     *
     * @param string $token
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function resetPasswordAction($token, Request $request, ObjectManager $entityManager, UserPasswordEncoderInterface $passwordEncoder, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        // look for an user with this reset token
        /** @var User $user */
        $user = $entityManager->getRepository('AppBundle:User')->findOneByResetToken($token);

        // if no user found redirect to the forgot password form
        if (null === $user) {
            $this->addFlash('warning', 'user.flash.forgot_password.error');
            return $this->redirectToRoute('user_forgot_password');
        }

        // display the new password form
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);

        // handle the form post
        if ($form->isSubmitted() && $form->isValid()) {

            // if user was not enabled, enable it and set redirection to profile page
            $redirectToProfile = false;
            if (!$user->isEnabled()) {
                $user->setEnabled(true);
                $user->setEnabledAt(new \DateTime());
                $user->setEnableToken(null);
                $eventDispatcher->dispatch(UserEvents::ENABLE_POST_UPDATE, new UserEvent($user));
                $redirectToProfile = true;
            }

            // encode the new password
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // reset the reset password properties
            $user->setResetToken(null);

            // implicit login
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
            $tokenStorage->setToken($token);

            // dispatch an event just like when the user logs in via the form
            $event = new InteractiveLoginEvent($request, $token);
            $eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

            // redirect to the dashboard page or the profile page
            if ($redirectToProfile) {
                $this->addFlash('success', 'user.flash.enabled');
                return $this->redirectToRoute('user_profile');
            } else {
                $this->addFlash('success', 'user.flash.password_reset');
                return $this->redirectToRoute('user_dashboard');
            }
        }

        return $this->render('front/user/reset_password.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/", name="user_dashboard")
     *
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardAction(ObjectManager $entityManager)
    {
        // get customer orders
        $orders = $entityManager->getRepository('AppBundle:Order')->findOrdersForCustomerOnDashboard($this->getUser());

        // get customer orders that are delivered but not closed (meaning awaiting a review)
        $ordersAwaitingReview = $entityManager->getRepository('AppBundle:Order')->findOrdersAwaitingReviewForCustomerOnDashboard($this->getUser());

        // get customer projects
        $projects = $entityManager->getRepository('AppBundle:Project')->findProjectsForCustomerOnDashboard($this->getUser());

        // get the latest customer private messages: only keep messages that were last sent by the maker, and with appropriate order/project status
        $userMessages = $entityManager->getRepository('AppBundle:Message')->findLatestMessagesReceivedByUser($this->getUser());
        $messages = new ArrayCollection();
        $ignoredOrders = array();
        $ignoredQuotations = array();
        foreach($userMessages as $message) {
            /** @var Message $message */
            if (null !== $message->getOrder()) {
                if (in_array($message->getOrder()->getReference(), $ignoredOrders)) {
                    continue;
                } else {
                    if ($message->isAuthorMaker() && $message->getOrder()->getStatus() !== Order::STATUS_CLOSED && $message->getOrder()->getStatus() !== Order::STATUS_REFUNDED && $message->getOrder()->getStatus() !== Order::STATUS_DELIVERED) {
                        $messages->add($message);
                    }
                    $ignoredOrders[] = $message->getOrder()->getReference();
                }
            } elseif (null !== $message->getQuotation()) {
                if (in_array($message->getQuotation()->getReference(), $ignoredQuotations)) {
                    continue;
                } else {
                    if ($message->isAuthorMaker() && $message->getQuotation()->getProject()->getStatus() !== Project::STATUS_DELETED && $message->getQuotation()->getProject()->getStatus() !== Project::STATUS_ORDERED && $message->getQuotation()->getProject()->getStatus() !== Project::STATUS_CANCEL) {
                        $messages->add($message);
                    }
                    $ignoredQuotations[] = $message->getQuotation()->getReference();
                }
            }
        }

        // get quotation agreement parameter (to calculate quotation validity date)
        $quotationAgreementDays = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::QUOTATION_AGREEMENT_TIME)->getValue();

        // get maker orders
        $makerOrders = null;
        if (null !== $this->getUser()->getMaker()) {
            $makerOrders = $entityManager->getRepository('AppBundle:Order')->findOrdersForMakerOnDashboard($this->getUser()->getMaker());
        }

        // get maker quotations
        $makerQuotations = null;
        if (null !== $this->getUser()->getMaker()) {
            $makerQuotations = $entityManager->getRepository('AppBundle:Quotation')->findQuotationsForMakerOnDashboard($this->getUser()->getMaker());
        }

        // get the latest maker private messages: only keep messages that were last sent by the maker, and with appropriate order/project status
        $makerMessages = null;
        if (null !== $this->getUser()->getMaker()) {
            $mMessages = $entityManager->getRepository('AppBundle:Message')->findLatestMessagesReceivedByMaker($this->getUser()->getMaker());
            $makerMessages = new ArrayCollection();
            $ignoredOrders = array();
            $ignoredQuotations = array();
            foreach ($mMessages as $message) {
                /** @var Message $message */
                if (null !== $message->getOrder()) {
                    if (in_array($message->getOrder()->getReference(), $ignoredOrders)) {
                        continue;
                    } else {
                        if (!$message->isAuthorMaker() && $message->getOrder()->getStatus() !== Order::STATUS_CLOSED && $message->getOrder()->getStatus() !== Order::STATUS_REFUNDED && $message->getOrder()->getStatus() !== Order::STATUS_TRANSIT && $message->getOrder()->getStatus() !== Order::STATUS_DELIVERED && $message->getOrder()->getStatus() !== Order::STATUS_PND && $message->getOrder()->getStatus() !== Order::STATUS_CLOSED) {
                            $makerMessages->add($message);
                        }
                        $ignoredOrders[] = $message->getOrder()->getReference();
                    }
                } elseif (null !== $message->getQuotation()) {
                    if (in_array($message->getQuotation()->getReference(), $ignoredQuotations)) {
                        continue;
                    } else {
                        if (!$message->isAuthorMaker() && $message->getQuotation()->getStatus() !== Quotation::STATUS_REFUSED && $message->getQuotation()->getStatus() !== Quotation::STATUS_CLOSED && $message->getQuotation()->getStatus() !== Quotation::STATUS_ACCEPTED && $message->getQuotation()->getStatus() !== Quotation::STATUS_DISCARDED) {
                            $makerMessages->add($message);
                        }
                        $ignoredQuotations[] = $message->getQuotation()->getReference();
                    }
                }
            }
        }

        return $this->render('front/user/dashboard.html.twig', array(
            'orders'   => $orders,
            'ordersAwaitingReview' => $ordersAwaitingReview,
            'projects' => $projects,
            'messages' => $messages,
            'quotationAgreementDays' => $quotationAgreementDays,
            'makerOrders'     => $makerOrders,
            'makerQuotations' => $makerQuotations,
            'makerMessages'   => $makerMessages
        ));
    }

    /**
     * @Route("/mon-compte-client/modifier", name="user_profile")
     *
     * @param Request $request
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function profileAction(Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // update the associated maker account if it exists
            if (null !== $user->getMaker()) {
                $user->getMaker()->setFirstname($user->getFirstname());
                $user->getMaker()->setLastname($user->getLastname());
            }

            // update the user
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // dispatch an event
            $eventDispatcher->dispatch(UserEvents::PROFILE_POST_UPDATE, new UserEvent($user));

            // redirect
            $this->addFlash('success', 'user.flash.updated');
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('front/user/profile.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/password/modifier", name="user_password_profile")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function passwordAction(Request $request, ObjectManager $entityManager, UserPasswordEncoderInterface $passwordEncoder, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $user = $this->getUser();

        if (null === $user) {
            return $this->redirectToRoute('user_login');
        }
        
        $form = $this->createForm(UserModifyPasswordType::class, $user);

        $form->handleRequest($request);

        // handle the form post
        if ($form->isSubmitted() && $form->isValid()) {

            // encode the new password
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // reset the reset password properties
            $user->setResetToken(null);

            // implicit login
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
            $tokenStorage->setToken($token);

            // dispatch an event just like when the user logs in via the form
            $event = new InteractiveLoginEvent($request, $token);
            $eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

            // redirect to the dashboard page
            $this->addFlash('success', 'user.flash.password_reset');
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('front/user/profile_password.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/ma-societe", name="user_maker_setup")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param StripeManager $stripeManager
     * @return Response
     */
    public function setupMakerAction(Request $request, ObjectManager $entityManager, TokenStorageInterface $tokenStorage, StripeManager $stripeManager)
    {
        // get the current user & maker
        /** @var User $user */
        $user = $this->getUser();
        $maker = $user->getMaker();
        $accountStatus="";
        $isNewMaker = false;

        if (null === $maker) {
            // initialize the maker instance
           // $this->addFlash('danger', 'nouveau maker');
            $maker = new Maker();
            $maker->setFirstname($user->getFirstname());
            $maker->setLastname($user->getLastname());
            $maker->setCompany($user->getCompany());
            $maker->setCompanyType($user->getCompanyType());
            if (null !== $user->getDefaultBillingAddress()) {
                $maker->setAddress(clone $user->getDefaultBillingAddress());// caution: we have to clone to prevent user address update
            } else {
                $makerAddress = new Address();
                $makerAddress->setFirstname($maker->getFirstname());
                $makerAddress->setLastname($maker->getLastname());
                $makerAddress->setCompany($maker->getCompany());
                $makerAddress->setCountry('FR');
                $maker->setAddress($makerAddress);
            }
            $maker->setUser($user);
            $isNewMaker = true;
        }

        if (null !== $maker->getStripeId()) {
            // acount strip exists - verify if the account is ok 
            try {
                $this->logger->info('API USER maker : le compte Stripe Existe');
                $account = $stripeManager->getAccount($maker->getStripeId());
               
                if (null !== $maker->getStripeRepresentativeId()) {
                    // Representant is create - verifiy if he is ok
                   
                    $person = $stripeManager->getPersonRepresentative($account->id,$maker->getStripeRepresentativeId());
                  
                    if ($account->payouts_enabled && $account->capabilities->transfers = "active" && $person->verification->status = "active") {
                        $accountStatus = "Stripe.Account.Status.verifyOK" ;
                    }else {
                        if ($person->verification->document->details_code != null){
                            $this->addFlash('danger','Stripe.Account.PapierIdentityMissing');
                        }else{
                            $accountStatus = 'Stripe.Account.Status.VerifyPending';

                        }
      
                    }
                }else
                {
                    $this->logger->info('API USER maker : le Representant Stripe n\'existe pas');
                    // The Representant is missing !! first pb
                    $this->addFlash('danger','Stripe.Account.StripeRepresentativeMissing');
                }       
            } catch (\Exception $e) {
                $this->addFlash('danger','Compte en erreur - Merci de contacter l\'administrateur');
            }
        }



        // hide identity paper field if one is already set
        $hideIdentityPaperField = false;
        if (null !== $maker->getIdentityPaperStripeId()) {
            $hideIdentityPaperField = true;
        }

        // create the form
        $form = $this->createForm(MakerType::class, $maker, array(
            'required_iban'           => $isNewMaker || null === $maker->getStripeId(),
            'required_identity_paper' => $isNewMaker || null === $maker->getStripeId(),
            'label_iban'              => $isNewMaker || null === $maker->getStripeId() ? 'IBAN' : 'Nouvel IBAN',
            'hide_identity_paper'     => $hideIdentityPaperField
        ));
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            // update maker address firstname and lastname
            $maker->getAddress()->setFirstname($maker->getFirstname());
            $maker->getAddress()->setLastname($maker->getLastname());

            // get IBAN value
            $iban = $form->get('iban')->getData();

            // Stripe connection
            $isStripeError = false;
            $deleteOldBankAccount = false;
            $oldBankAccountId = null;
            //$this->addFlash('danger', sprintf('Id StripeID = %s',$maker->getStripeId()));

            if (null === $maker->getStripeId()) {
                // create a Stripe Account with person representative
                try {
                    // create account connect in stripe (the company)
                    
                    $account = $stripeManager->createAccount($maker,'EUR', $iban);
                    if ($account instanceof Account){
                        $maker->setStripeId($account->id);
                        $bankAccount = $account->external_accounts->data[0];
                        $maker->setStripeBankAccountId($bankAccount->id);
                        $maker->setStripeBankAccountIbanLast4($bankAccount->last4);
                        $maker->setStripeBankAccountIbanBankName($bankAccount->bank_name);
                    } else {
                        $isStripeError = true;
                    }
                    //$this->addFlash('danger', sprintf('Avant creation du Representant. IsStripeError = %b',$isStripeError));
                    // create person representative in stripe
                    $person = $stripeManager->createPersonRepresentative($maker);
                    if ($person instanceof Person){
                        $maker->setStripeRepresentativeId($person->id);
                    } else {
                        $isStripeError = true;
                    }
                    //$this->addFlash('danger', sprintf('Apres creation du Representant. IsStripeError = %b',$isStripeError));
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                    $isStripeError = true;
  
                }
            } else {
                // retrieve current Stripe Account
                try {
                    $account = $stripeManager->getAccount($maker->getStripeId());
                    if ($account instanceof Account) {
                        
                        $this->logger->info(sprintf('API USER maker : le compte stripe : %s',$account->id));

                        // update IBAN if the field is filled
                        if (null !== $iban && '' !== $iban) {
                            $oldBankAccountId = $maker->getStripeBankAccountId();// won't be used, see below note
                            $deleteOldBankAccount = true;// won't be used, see below note
                            try {
                                $updatedStripeAccount = $stripeManager->createBankAccount(
                                    $maker->getStripeId(),
                                    $iban,
                                    'EUR'
                                );
                                $updatedStripeAccount = $stripeManager->getAccount($maker->getStripeId()); // we must reload to get proper updated data
                                $newBankAccount = $updatedStripeAccount->external_accounts->data[0];
                                $maker->setStripeBankAccountId($newBankAccount->id);
                                $maker->setStripeBankAccountIbanLast4($newBankAccount->last4);
                                $maker->setStripeBankAccountIbanBankName($newBankAccount->bank_name);
                            } catch (\Exception $e) {
                                $isStripeError = true;
                                $deleteOldBankAccount = false;
                            }
                        }       

                        //Create representative if he doesn't exist
                        if (null == $maker->getStripeRepresentativeId()) {
                            // create person representative in stripe
                            try {
                                $this->logger->info(sprintf('API USER maker : Avant creation du representant '));
                                $person = $stripeManager->createPersonRepresentative($maker);
                                $this->logger->info(sprintf('API USER maker : apres creation du representant %s',$person));
                                if ($person instanceof Person){
                                    $maker->setStripeRepresentativeId($person->id);
                                } else {
                                    $isStripeError = true;
                                }
                            } catch (\Exception $e) {
                                $isStripeError = true;
                            }
                        }

                    } else {
                        $isStripeError = true;
                    }
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                    $isStripeError = true;
                }
            }

            // default redirect route
            $redirectRoute = 'user_maker_setup';
           // $this->addFlash('danger', sprintf('Is Stripe error= %b',$isStripeError));
            if (!$isStripeError) {

                // persist the entity, and flush now to provoke proper handling of file upload
                if ($isNewMaker) {
                    $entityManager->persist($maker);// could be a good idea to delete the entity if anything goes wrong afterwards...
                }
                $entityManager->flush();

                if (null !== $maker->getIdentityPaperFile()) {
                    $filePath = $this->get('kernel')->getProjectDir().'/var/uploads/maker/identy-paper/' . $maker->getIdentityPaperName();
                    $fileVersoPath = $this->get('kernel')->getProjectDir().'/var/uploads/maker/identy-paper/' . $maker->getIdentityPaperNameVerso();
                    
                    $identity = null;
                    $identityVerso = null;
                    try {
                        $identity = $stripeManager->createDocument($filePath,'identity_document');
                        $identityVerso = $stripeManager->createDocument($fileVersoPath,'identity_document');
                    } catch(\Exception $e) {
                        $this->addFlash('danger', $e->getMessage());
                        $isStripeError = true;
                    }
                    //$this->addFlash('danger', sprintf('Identityfile  ID = %s',$identity->id));
                    if (null !== $identity && $identity->id && $identityVerso && $identityVerso->id) {
                        try {
                            //$this->addFlash('danger', sprintf('Juste avant de lier le fichier avec le representant  ID '));
                            $accountUpdated = $stripeManager->updateAccountWithIdentity($maker->getStripeId(),$maker->getStripeRepresentativeId(), $identity->id,$identityVerso->id );
                            $maker->setIdentityPaperStripeId($identity->id);
                            $maker->setIdentityPaperVersoStripeId($identityVerso->id);
                            //On desative le controle historique sur les élements nécessaires pour vraiment rendre le compte opérationnel.
                            //if (count($accountUpdated->verification->fields_needed) == 0 || ($accountUpdated->verification->fields_needed[0] == 'legal_entity.additional_owners') && count($accountUpdated->verification->fields_needed) == 1) {

                                $maker->setEnabled(true);

                                // reload user permissions if this is a new maker, to get the proper roles
                                if ($isNewMaker) {
                                    $token = new UsernamePasswordToken(
                                        $user,
                                        $user->getPassword(),
                                        'main',
                                        $user->getRoles()
                                    );
                                    $tokenStorage->setToken($token);
                                }

                            /*} else {
                                $isStripeError = true;
                            }*/
                        } catch (\Exception $e) {
                            $this->addFlash('danger', $e->getMessage());
                            $isStripeError = true;
                        }
                    } else {
                        $isStripeError = true;
                    }
                }

                // delete old bank account if needed
                // Note: this does not seem necessary as the update of the account has already deleted the old one
                // if we try to delete, Stripe throws an exception "The bank account <$oldBankAccountId> has been deleted and can no longer be used."
                // Potential issue: if the flush is not done, the bank account would still have been deleted on Stripe end...
                /*
                if (!$isStripeError && $deleteOldBankAccount && null !== $oldBankAccountId) {
                    try {
                        $stripeManager->deleteBankAccount($maker->getStripeId(), $oldBankAccountId);
                    } catch(\Exception $e) {
                        $this->addFlash('danger', $e->getMessage());
                        $isStripeError = true;
                    }
                }
                */

                // flush
                if (!$isStripeError) {
                    if ($isNewMaker) {
                        $this->addFlash('success', 'maker.flash.maker_created');
                        $redirectRoute = 'user_maker_details_edit';
                    } else {
                        $this->addFlash('success', 'maker.flash.maker_updated');
                    }
                    $entityManager->flush();
                } else {
                    $this->addFlash('danger', 'Une erreur est survenue, veuillez ré-essayer ou nous contacter si l\'erreur persiste.');
                }

            }

            // redirect to the appropriate route
            return $this->redirectToRoute($redirectRoute);
        }


        return $this->render('front/user/maker/form.html.twig', array('form' => $form->createView(), 'maker' => $maker, 'edit' => !$isNewMaker,'status'=>$accountStatus));
    }

    /**
     * @Route("/mon-compte-maker/mes-informations-commerciales", name="user_maker_details_edit")
     *
     * @param Request $request
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function editMakerDetailsAction(Request $request)
    {
        // get the current user
        $user = $this->getUser();

        // get the maker
        $maker = $user->getMaker();

        // create the form
        $form = $this->createForm(MakerDetailsType::class, $maker);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            // flush
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // redirect
            $this->addFlash('success', 'maker.flash.maker_updated');
            return $this->redirectToRoute('user_maker_details_edit');
        }

        // render the form
        return $this->render('front/user/maker/details_form.html.twig', array('form' => $form->createView(),'maker' => $maker));
    }

    /**
     * @Route("/mon-compte-maker/mes-coordonnees-bancaires", name="user_maker_bank_setup")
     * Fonction desactivé. La saisie des coordonnées bancaire et piece d identité est faite a la creation de la société.
     * @param Request $request
     * @param StripeManager $stripeManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     * @deprecated
     */
    public function makerBankSetupAction(Request $request,StripeManager $stripeManager)
    {
        // get the current user
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        // get the maker
        /** @var Maker $maker */
        $maker = $user->getMaker();

        if($maker->getStripeId()){

            try {

                $account = $stripeManager->getAccount($maker->getStripeId());

                if(in_array('legal_entity.verification.document', $account->verification->fields_needed)){

                    $this->addFlash('danger', 'Merci de fournir une pièce d\'identité valide et lisible');

                }
                
            } catch (Exception $e) {
                
            }

        }

        // create the form
        $form = $this->createForm(MakerBankSetupType::class, $maker);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            $created = false;
            $errorCreating = false;
            $errorUpdating = false;

            if(!$maker->getStripeId()){

                // create Stripe Account
                try {

                    $account = $stripeManager->createAccount($maker,'eur', $request->request->get('maker_bank_setup')['iban']);

                    if ($account instanceof Account){

                        $maker->setStripeId($account->id);
                        
                        $bankAccount = $account->external_accounts->data[0];

                        $maker->setStripeBankAccountId($bankAccount->id);
                        $maker->setStripeBankAccountIbanLast4($bankAccount->last4);
                        $maker->setStripeBankAccountIbanBankName($bankAccount->bank_name);

                        $em->persist($maker);
                        $created = true;

                    }
                        
                } catch (\Exception $e) {

                    $this->addFlash('danger', $e->getMessage());

                    $errorCreating = true;
                        
                }

            } else {

                // retrieve current Stripe Account
                try {

                    $account = $stripeManager->getAccount($maker->getStripeId());

                    if (!$account instanceof Account){
                             
                        $this->addFlash('danger', 'Problème sur le compte maker, veuillez nous contacter.');

                        $errorUpdating = true;

                    } else {

                        $oldBankAccountId = $maker->getStripeBankAccountId();

                        $newBankAccount = $stripeManager->createBankAccount(
                            $maker->getStripeId(),
                            $request->request->get('maker_bank_setup')['iban'],
                            'EUR'
                        );

                        $stripeManager->deleteBankAccount($maker->getStripeId(), $oldBankAccountId);

                        $account = $stripeManager->getAccount($maker->getStripeId());

                        $bankAccount = $account->external_accounts->data[0];

                        $maker->setStripeBankAccountId($bankAccount->id);
                        $maker->setStripeBankAccountIbanLast4($bankAccount->last4);
                        $maker->setStripeBankAccountIbanBankName($bankAccount->bank_name);

                        $em->persist($maker);

                    }
                        
                } catch (\Exception $e) {

                    $this->addFlash('danger', $e->getMessage());

                    $errorUpdating = true;
                        
                }

            }

            // enable maker if we successfully created the stripe account
            /*if ($created) {
                $maker->setEnabled(true);
                $this->addFlash('success', 'maker.flash.enabled');
            }*/
            
            // flush
            //$em = $this->getDoctrine()->getManager();
            $em->flush();


            if (!$errorCreating && !$errorUpdating) {

                /* Passage desactivé - si il fallait la reactivé prevoir d'ajouter le verso de piece d'identité
                if($maker->getIdentityPaperStripeId() == null && $maker->getIdentityPaperName()){

                    $filePath = $this->get('kernel')->getProjectDir().'/var/uploads/maker/identy-paper/'.$maker->getIdentityPaperName();
                    $identity = $stripeManager->createDocument($filePath,'identity_document');

                    if($identity->id){
                        $accountUpdated = $stripeManager->updateAccountWithIdentity($maker->getStripeId(),$maker->getStripeRepresentativeId(), $identity->id);

                        $maker->setIdentityPaperStripeId($identity->id);

                        if(count($accountUpdated->verification->fields_needed) == 0 || ($accountUpdated->verification->fields_needed[0] == 'legal_entity.additional_owners') && count($accountUpdated->verification->fields_needed) == 1 ){

                            $maker->setEnabled(true);
                            $this->addFlash('success', 'maker.flash.enabled');

                        } else {

                            $this->addFlash('danger', 'maker.flash.bank_setup_error');

                        }

                        $em->persist($maker);
                        $em->flush();

                    }

                    

                } else {

                    $this->addFlash('success', 'maker.flash.bank_setup_updated');

                }
                */
            } else {
                $this->addFlash('danger', 'maker.flash.bank_setup_error');
            }

            // redirect
            return $this->redirectToRoute('user_maker_bank_setup');
        }

        // display data
        $name = $maker->getFullname();

        if($maker->getStripeBankAccountIbanLast4()){
            $ibanLast4 = $maker->getStripeBankAccountIbanLast4();
        } else {
            $ibanLast4 = false;
        }

        if($maker->getStripeBankAccountIbanBankName()){
            $ibanBank = $maker->getStripeBankAccountIbanBankName();
        } else {
            $ibanBank = false;
        }

        $ibanReady = false;

        if($ibanBank != false || $ibanLast4 != false){
            $ibanReady = true;
        }


        //$payout = $stripeManager->createPayoutBankAccount(200,'EUR','acct_1CYjxBApY6jxuJEH','ba_1CYjxBApY6jxuJEHJ4V5edVY','125');

        // render the form
        return $this->render('front/user/maker/bank_setup.html.twig', array(
            'name' => $name,
            'iban_bank'=> $ibanBank, 
            'iban_last4'=> $ibanLast4,
            'iban_ready'=> $ibanReady,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'email' => $maker->getUser()->getEmail(),
            'form' => $form->createView())
        );
    }

    /**
     * @Route("/mon-compte-maker/mes-modeles-3D", name="user_maker_creations")
     *
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerListAction(ObjectManager $entityManager)
    {
        //$orders = $entityManager->getRepository('AppBundle:Order')->findOrdersForCustomer($this->getUser());
        
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();
        
        $models = $entityManager->getRepository('AppBundle:Model')->findModelsNotDeletedForMaker($this->getUser()->getMaker());
        return $this->render('front/user/maker/model/list.html.twig', array(
            'models' => $models,
            'maker' => $maker
        ));
    }

    /**
     * @Route("/mon-compte-maker/nouveau-modele-3D", name="user_maker_creations_new")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerAddModelAction(Request $request, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        $model = new Model();
        
        $status = $entityManager->getRepository('AppBundle:ModelStatus')->findAll();
        //$defaultStatus= $status[0];
        //$model->setStatus($defaultStatus);

        $licenses = $entityManager->getRepository('AppBundle:ModelLicense')->findAll();
        
        $addModelForm = $this->createForm(MakerAddModelType::class, $model);
        
        $addModelForm->remove('correctionReason');

        $addModelForm->handleRequest($request);

        if ($addModelForm->isSubmitted() && $addModelForm->isValid()) {
            if ($addModelForm->getClickedButton() && 'add' === $addModelForm->getClickedButton()->getName()) {
                $model->setMaker($maker);
                $priceTaxExcl = $model->getPriceTaxExcl();

    
                $model->setPriceTaxIncl($priceTaxExcl*1.2);
                $model->setStatus($status[0]);

                // flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($model);
                $em->flush();

                // redirect
                $this->addFlash('success', 'model.flash.created');
                return $this->redirectToRoute('user_maker_creations');
            }
            elseif ($addModelForm->getClickedButton() && 'save' === $addModelForm->getClickedButton()->getName()) {
                $model->setMaker($maker);
                $priceTaxExcl = $model->getPriceTaxExcl();

                
                $model->setPriceTaxIncl($priceTaxExcl*1.2);
                $model->setStatus($status[3]);

                // flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($model);
                $em->flush();

                // redirect
                $this->addFlash('success', 'model.flash.created');
                //return $this->redirectToRoute('user_maker_creations');
                // redirect
                return $this->redirectToRoute('user_maker_model_modify', array(
                    'id' => $model->getId(),
                ));
            }
        }

        $AddModelFormView = $addModelForm->createView();
        
        

        return $this->render('front/user/maker/model/formModel.html.twig', array(
            'form' => $AddModelFormView,
            'licenses' => $licenses,
            'model_name' => null,
            'model' => $model,
            'maker' => $maker,
            'signalExist' => false
        ));
    }

    /**
     * @Route("/mon-compte-maker/modifier-modele-3D/{id}", name="user_maker_model_modify")
     *
     * @param Model $model
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function MakerModelModifyAction(Model $model, Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();
        
        $licenses = $entityManager->getRepository('AppBundle:ModelLicense')->findAll();
        $status = $entityManager->getRepository('AppBundle:ModelStatus')->findAll();


        $modifyModelForm = $this->createForm(MakerAddModelType::class, $model);

        //In the form to modify a model, the maker cannot change the name and the file of the model
        $modifyModelForm->remove('attachmentFile');

        if ($model->getCorrectionReason() === null) {
            $modifyModelForm->remove('correctionReason');
        }

        $modifyModelForm->handleRequest($request);

        //echo("<script>console.log('coucou:".$model->getPortfolioImages()[0]->getPictureFile()."');</script>");
        $portfolio = $model->getPortfolioImages();
        $image = $liipImagineCacheManager->getBrowserPath($portfolio[0]->getPictureName(), 'model_portfolio');
            

        if ($modifyModelForm->isSubmitted() && $modifyModelForm->isValid()) {
            if ($modifyModelForm->getClickedButton() && 'add' === $modifyModelForm->getClickedButton()->getName()) {
                $priceTaxExcl = $model->getPriceTaxExcl();
    
                $model->setPriceTaxIncl($priceTaxExcl*1.2);
                $model->setStatus($status[0]);

                // flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($model);
                $em->flush();

                // redirect
                $this->addFlash('success', 'model.flash.updated');
                return $this->redirectToRoute('user_maker_creations');
            }
            elseif ($modifyModelForm->getClickedButton() && 'save' === $modifyModelForm->getClickedButton()->getName()) {
                $priceTaxExcl = $model->getPriceTaxExcl();

                $model->setPriceTaxIncl($priceTaxExcl*1.2);
                $model->setStatus($status[3]);

                // flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($model);
                $em->flush();

                // redirect
                $this->addFlash('success', 'model.flash.updated');
                return $this->redirectToRoute('user_maker_creations');
            }
        }
        
        $ModifyModelFormView = $modifyModelForm->createView();

        $signalExist = false;
        foreach($model->getModelSignal() as $signal) {
            if($signal->getStatus() === Signal::STATUS_VALID ) {
                $signalExist = true;
            }
        }

        return $this->render('front/user/maker/model/formModel.html.twig', array(
            'form' => $ModifyModelFormView,
            'licenses' => $licenses,
            'model_name' => $model->getName(),
            'model' => $model,
            'image' => $image,
            'maker' => $maker,
            'signalExist' => $signalExist
        ));
    }


    /**
     * @Route("/mon-compte-maker/modifier-modele-3D/{id}/supprimer", requirements={"id" = "\d+"}, name="model_delete")
     *
     * @param Model $model
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function deleteAction(Model $model, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();
        $status = $entityManager->getRepository('AppBundle:ModelStatus')->findAll();

        if($model->getMaker() == $maker){

            //$entityManager->remove($model);
            $model->setStatus($status[4]);
            $entityManager->flush();
            $this->addFlash('success', 'Modèle supprimée avec succès');

            return $this->redirectToRoute('user_maker_creations');

        } else {

            $this->addFlash('danger', 'Erreur lors de la suppression');

            return $this->redirectToRoute('user_maker_creations');


        }

    }

    /**
     * @Route("/{name}/{id}/makerdownload/{order_id}", name="user_maker_download")
     *
     * @param Model $model
     * @param Int $order_id
     * @return BinaryFileResponse
     */
    public function orderFileAdminDownloadAction(Int $order_id, Model $model, ObjectManager $entityManager)
    {
        $order = $entityManager->getRepository('AppBundle:Order')->find($order_id);
        if($order !== null) {
            $modelBuys = $entityManager->getRepository('AppBundle:ModelBuy')->findModelBuyFromOrderAndModel($order, $model);
            if(sizeof($modelBuys) > 0) {
                
                $modelBuy = $modelBuys[0];

                /** @var User $maker */
                $user = $this->getUser();

                if(($modelBuy->getCustomer() === $user) && $order->getStatus() !== Order::STATUS_CANCELED  && $order->getStatus() !== Order::STATUS_REFUNDED ) {
                    // get file from project directory
                    $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/model/attachment/' . $model->getAttachmentName());

                    // prevent caching
                    $response->setPrivate();
                    $response->setMaxAge(0);
                    $response->setSharedMaxAge(0);
                    $response->headers->addCacheControlDirective('must-revalidate', true);
                    $response->headers->addCacheControlDirective('no-store', true);

                    // force file download
                    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $model->getAttachmentOriginalName());
                    return $response;
                } else {
                    return $this->redirectToRoute('user_dashboard');
                }
            } else {
                return $this->redirectToRoute('user_dashboard');
            }
        } else {
            return $this->redirectToRoute('user_dashboard');
        }
    }
}