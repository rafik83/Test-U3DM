<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Maker;
use AppBundle\Entity\User;
use AppBundle\Entity\Address;
use AppBundle\Event\UserEvent;
use AppBundle\Event\UserEvents;
use AppBundle\Form\MakerBankSetupType;
use AppBundle\Form\MakerType;
use AppBundle\Form\UserProfileType;
use AppBundle\Form\UserRegistrationType;
use AppBundle\Form\UserResetPasswordType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;
use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices;

/**
 * @Route("/api/user")
 */
class ApiUserController extends Controller
{
    
    /**
     * @Route("/login", name="api_user_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
 
        if($this->getUser()){

            $user = $this->getUser();

            $data = [];
            $data['id'] = $user->getId();
            $data['firstname'] = $user->getFirstname();
            $data['lastname'] = $user->getLastname();
            $data['email'] = $user->getEmail();
            $data['sepa'] = $user->hasSepa();
            $data['maker'] = ($this->getUser()->getMaker() != null);;

            if($user->getDefaultBillingAddress()){

                $data['address_billing']['firstname'] = $user->getDefaultBillingAddress()->getFirstname();
                $data['address_billing']['lastname'] = $user->getDefaultBillingAddress()->getLastname();
                $data['address_billing']['street1'] = $user->getDefaultBillingAddress()->getStreet1();

                if($user->getDefaultBillingAddress()->getStreet2())
                    $data['address_billing']['street2'] = $user->getDefaultBillingAddress()->getStreet2();

                $data['address_billing']['zipcode'] = $user->getDefaultBillingAddress()->getZipcode();
                $data['address_billing']['city'] = $user->getDefaultBillingAddress()->getCity();
                $data['address_billing']['country'] = $user->getDefaultBillingAddress()->getCountry();
                $data['address_billing']['phone'] = $user->getDefaultBillingAddress()->getTelephone();

                if($user->getDefaultBillingAddress()->getCompany())
                    $data['address_billing']['company'] = $user->getDefaultBillingAddress()->getCompany();
            }

            if($user->getDefaultShippingAddress()){
                $data['address_shipping']['firstname'] = $user->getDefaultShippingAddress()->getFirstname();
                $data['address_shipping']['lastname'] = $user->getDefaultShippingAddress()->getLastname();
                $data['address_shipping']['street1'] = $user->getDefaultShippingAddress()->getStreet1();

                if($user->getDefaultShippingAddress()->getStreet2())
                    $data['address_shipping']['street2'] = $user->getDefaultShippingAddress()->getStreet2();

                $data['address_shipping']['zipcode'] = $user->getDefaultShippingAddress()->getZipcode();
                $data['address_shipping']['city'] = $user->getDefaultShippingAddress()->getCity();
                $data['address_shipping']['country'] = $user->getDefaultShippingAddress()->getCountry();
                $data['address_shipping']['phone'] = $user->getDefaultShippingAddress()->getTelephone();

                if($user->getDefaultShippingAddress()->getCompany())
                    $data['address_shipping']['company'] = $user->getDefaultShippingAddress()->getCompany();
            }

            return new JsonResponse(['data' => $data ],200);

        } else {

            return new JsonResponse(['error' => 'Error Login' ],500);

        }
        
    }

    /**
     * @Route("/logout", name="api_user_logout")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function logoutAction(Request $request)
    {
    
        // Logging user out.
        $this->get('security.token_storage')->setToken(null);

        // Invalidating the session.
        $session = $request->getSession();
        $session->invalidate();

        return new JsonResponse(['data' => 'success' ],200);
        
    }

    /**
     * @Route("/register", name="api_user_register")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, EventDispatcherInterface $eventDispatcher)
    {
        // @see http://symfony.com/doc/current/doctrine/registration_form.html

        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // quick request content format check
        if (!isset($requestContent['lastname']) || !isset($requestContent['firstname']) || !isset($requestContent['email']) || !isset($requestContent['password']) || !isset($requestContent['newsletter'])) {
            return new JsonResponse(json_encode(array('Error' => 'Missing arguments')),500);
        }

        $user = new User();

        // encode the password
        $requestContent['password'] = $passwordEncoder->encodePassword($user, $requestContent['password']);
        $user->setPassword($requestContent['password']);

        // set Data
        $user->setFirstname($requestContent['firstname']);
        $user->setLastname($requestContent['lastname']);
        $user->setEmail($requestContent['email']);
        $user->setNewsletter($requestContent['newsletter']);

        if(isset($requestContent['address1']) && isset($requestContent['zipcode']) && isset($requestContent['city']) && isset($requestContent['country']) && isset($requestContent['phone'])){

            $address = new Address();
            $address->setFirstname($requestContent['firstname']);
            $address->setLastname($requestContent['lastname']);
            if(isset($requestContent['company'])){
                $address->setCompany($requestContent['company']);
                $user->setCompany($requestContent['company']);
                $user->setType(User::TYPE_COMPANY);
            } else {
                $user->setType(User::TYPE_INDIVIDUAL);
            }
            $address->setStreet1($requestContent['address1']);

            if(isset($requestContent['address2'])){
                $address->setStreet2($requestContent['address2']);
            }
            $address->setZipcode($requestContent['zipcode']);
            $address->setCity($requestContent['city']);
            $address->setCountry($requestContent['country']);
            $address->setTelephone($requestContent['phone']);

            $user->setDefaultShippingAddress($address);
            $user->setDefaultBillingAddress(clone $address);

        }

        //Address Shipping / Billing


        // send an event
        $eventDispatcher->dispatch(UserEvents::REGISTER_PRE_PERSIST, new UserEvent($user));

        // persist
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // send an event
        $eventDispatcher->dispatch(UserEvents::REGISTER_POST_PERSIST, new UserEvent($user));

        /*$data = ['user_id' => $user->getId(), "user_lastname" => $user->getLastname(),"user_firstname" => $user->getFirstname()];*/
        $data = [];
        $data['id'] = $user->getId();
        $data['firstname'] = $user->getFirstname();
        $data['lastname'] = $user->getLastname();
        $data['email'] = $user->getEmail();
        $data['sepa'] = $user->hasSepa();

        if($user->getDefaultBillingAddress()){

            $data['address_billing']['firstname'] = $user->getDefaultBillingAddress()->getFirstname();
            $data['address_billing']['lastname'] = $user->getDefaultBillingAddress()->getLastname();
            $data['address_billing']['street1'] = $user->getDefaultBillingAddress()->getStreet1();

            if($user->getDefaultBillingAddress()->getStreet2())
                $data['address_billing']['street2'] = $user->getDefaultBillingAddress()->getStreet2();

            $data['address_billing']['zipcode'] = $user->getDefaultBillingAddress()->getZipcode();
            $data['address_billing']['city'] = $user->getDefaultBillingAddress()->getCity();
            $data['address_billing']['country'] = $user->getDefaultBillingAddress()->getCountry();
            $data['address_billing']['phone'] = $user->getDefaultBillingAddress()->getTelephone();

            if($user->getDefaultBillingAddress()->getCompany())
                $data['address_billing']['company'] = $user->getDefaultBillingAddress()->getCompany();
        }

        if($user->getDefaultShippingAddress()){
            $data['address_shipping']['firstname'] = $user->getDefaultShippingAddress()->getFirstname();
            $data['address_shipping']['lastname'] = $user->getDefaultShippingAddress()->getLastname();
            $data['address_shipping']['street1'] = $user->getDefaultShippingAddress()->getStreet1();

            if($user->getDefaultShippingAddress()->getStreet2())
                $data['address_shipping']['street2'] = $user->getDefaultShippingAddress()->getStreet2();

            $data['address_shipping']['zipcode'] = $user->getDefaultShippingAddress()->getZipcode();
            $data['address_shipping']['city'] = $user->getDefaultShippingAddress()->getCity();
            $data['address_shipping']['country'] = $user->getDefaultShippingAddress()->getCountry();
            $data['address_shipping']['phone'] = $user->getDefaultShippingAddress()->getTelephone();

            if($user->getDefaultShippingAddress()->getCompany())
                $data['address_shipping']['company'] = $user->getDefaultShippingAddress()->getCompany();
        }

        return new JsonResponse($data,200);

    }

    // /**
    //  * @Route("/register/confirm", name="api_user_register_confirm")
    //  *
    //  * @return Response
    //  */
    // public function registerConfirmAction()
    // {
    //     return $this->render('front/user/register.html.twig');
    // }

    /**
     * @Route("/connected", name="api_user_connected")
     *
     * @return Response
     */
    public function connectedConfirmAction()
    {
        if($this->getUser()){

            $data = [];
            $data['id'] = $this->getUser()->getId();
            $data['firstname'] = $this->getUser()->getFirstname();
            $data['lastname'] = $this->getUser()->getLastname();
            $data['email'] = $this->getUser()->getEmail();
            $data['sepa'] = $this->getUser()->hasSepa();
            $data['maker'] = ($this->getUser()->getMaker() != null);

            if($this->getUser()->getDefaultBillingAddress()){

                $data['address_billing']['firstname'] = $this->getUser()->getDefaultBillingAddress()->getFirstname();
                $data['address_billing']['lastname'] = $this->getUser()->getDefaultBillingAddress()->getLastname();
                $data['address_billing']['street1'] = $this->getUser()->getDefaultBillingAddress()->getStreet1();

                if($this->getUser()->getDefaultBillingAddress()->getStreet2())
                    $data['address_billing']['street2'] = $this->getUser()->getDefaultBillingAddress()->getStreet2();

                $data['address_billing']['zipcode'] = $this->getUser()->getDefaultBillingAddress()->getZipcode();
                $data['address_billing']['city'] = $this->getUser()->getDefaultBillingAddress()->getCity();
                $data['address_billing']['country'] = $this->getUser()->getDefaultBillingAddress()->getCountry();
                $data['address_billing']['phone'] = $this->getUser()->getDefaultBillingAddress()->getTelephone();

                if($this->getUser()->getDefaultBillingAddress()->getCompany())
                    $data['address_billing']['company'] = $this->getUser()->getDefaultBillingAddress()->getCompany();
            }

            if($this->getUser()->getDefaultShippingAddress()){
                $data['address_shipping']['firstname'] = $this->getUser()->getDefaultShippingAddress()->getFirstname();
                $data['address_shipping']['lastname'] = $this->getUser()->getDefaultShippingAddress()->getLastname();
                $data['address_shipping']['street1'] = $this->getUser()->getDefaultShippingAddress()->getStreet1();

                if($this->getUser()->getDefaultShippingAddress()->getStreet2())
                    $data['address_shipping']['street2'] = $this->getUser()->getDefaultShippingAddress()->getStreet2();

                $data['address_shipping']['zipcode'] = $this->getUser()->getDefaultShippingAddress()->getZipcode();
                $data['address_shipping']['city'] = $this->getUser()->getDefaultShippingAddress()->getCity();
                $data['address_shipping']['country'] = $this->getUser()->getDefaultShippingAddress()->getCountry();
                $data['address_shipping']['phone'] = $this->getUser()->getDefaultShippingAddress()->getTelephone();

                if($this->getUser()->getDefaultShippingAddress()->getCompany())
                    $data['address_shipping']['company'] = $this->getUser()->getDefaultShippingAddress()->getCompany();
            }


            return new JsonResponse(['data' => $data ],200);

        } else {

            return new JsonResponse(['error' => 'Not logged' ],500);

        }
    }

    // /**
    //  * @Route("/register/enable/{token}", name="api_user_register_enable")
    //  * @Method({"GET"})
    //  *
    //  * @param string $token
    //  * @param Request $request
    //  * @param TokenStorageInterface $tokenStorage
    //  * @param EventDispatcherInterface $eventDispatcher
    //  * @return Response
    //  */
    // public function enableAction($token, Request $request, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    // {
    //     /** @var User|null $user */
    //     $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneByEnableToken($token);
    //     if (null === $user) {
    //         return $this->redirectToRoute('user_login');
    //     }

    //     // update the user
    //     $user->setEnabled(true);
    //     $user->setEnabledAt(new \DateTime());
    //     $user->setEnableToken(null);
    //     $this->getDoctrine()->getManager()->flush();

    //     // implicit login
    //     $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
    //     $tokenStorage->setToken($token);

    //     // dispatch an event just like when the user logs in via the form
    //     $event = new InteractiveLoginEvent($request, $token);
    //     $eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

    //     // send an event
    //     $eventDispatcher->dispatch(UserEvents::ENABLE_POST_UPDATE, new UserEvent($user));

    //     // redirect to the profile page
    //     $this->addFlash('success', 'user.flash.enabled');
    //     return $this->redirectToRoute('user_profile');
    // }

    /**
     * @Route("/forgot_password", name="api_user_forgot_password")
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
     * @Route("/forgot_password/confirmation", name="api_user_forgot_password_confirm")
     *
     * @return Response
     */
    public function forgotPasswordConfirmAction()
    {
        return $this->render('front/user/forgot_password.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="api_user_reset_password")
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

        return $this->render('front/user/reset_password.html.twig', array('form' => $form->createView()));
    }

}