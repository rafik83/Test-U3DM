<?php

namespace AppBundle\Controller;



use AppBundle\Entity\Coupon;
use AppBundle\Entity\Maker;
use AppBundle\Entity\Printer;
use AppBundle\Entity\Prospect;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Entity\Suggestion;
use AppBundle\Entity\ModerationRule;
use AppBundle\Entity\Quotation;
use AppBundle\Form\CouponType;
use AppBundle\Form\MakerCommissionRateType;
use AppBundle\Form\MakerDesignAutoModerationType;
use AppBundle\Form\SettingType;
use AppBundle\Form\UserSepaType;
use AppBundle\Form\ModelCommentType;
use AppBundle\Form\SuggestionType;
use AppBundle\Form\MessageType;
use AppBundle\Form\ModerationRuleType;
use AppBundle\Form\ModerationRulesType;
use AppBundle\Event\QuotationEvent;
use AppBundle\Service\MessageManager;
use AppBundle\Service\StripeManager;
use AppBundle\Service\QuotationManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
/**
 * @Route("/%app.admin_directory%")
 */
class AdminController extends Controller
{
    /**
     * @Route("/login", name="admin_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function adminLoginAction(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/", name="admin_dashboard")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function dashboardAction(ObjectManager $entityManager)
    {
        $lastProspects = $entityManager->getRepository('AppBundle:Prospect')->findBy(array(), array('createdAt' => 'DESC'), 5);
        $disabledTags = $entityManager->getRepository('AppBundle:Tag')->findBy(array('enabled' => false));
        $counterProspects = $entityManager->getRepository('AppBundle:Prospect')->countProspects();

        return $this->render('admin/dashboard/dashboard.html.twig', array(
            'lastProspects'    => $lastProspects,
            'disabledTags'     => $disabledTags,
            'counterProspects' => $counterProspects
        ));
    }

    /**
     * @Route("/prospect/list", name="admin_prospect_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function prospectListAction(ObjectManager $entityManager)
    {
        // get all Prospects
        $prospects = $entityManager->getRepository('AppBundle:Prospect')->findAll();

        // display the list
        return $this->render('admin/prospect/list.html.twig', array('prospects' => $prospects));
    }

    /**
     * @Route("/prospect/{id}", requirements={"id" = "\d+"}, name="admin_prospect_see")
     *
     * @param Prospect $prospect
     * @return Response
     */
    public function prospectSeeAction(Prospect $prospect)
    {
        return $this->render('admin/prospect/see.html.twig', array('prospect' => $prospect));
    }

    /**
          * @Route("/prospect/add", name="admin_prospect_add")
     *
     * @param ObjectManager $entityManager
     * @return Response     
     */
    public function prospectAddAction(ObjectManager $entityManager)
    {
        // TODO
    }

    /**
     * @Route("/prospect/{id}/edit", requirements={"id" = "\d+"}, name="admin_prospect_edit")
     *
     * @param Prospect $prospect
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function prospectEditAction(Prospect $prospect, ObjectManager $entityManager)
    {
        // TODO
    }

    /**
     * @Route("/user/list", name="admin_user_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function userListAction(ObjectManager $entityManager)
    {
        // get all users
        $users = $entityManager->getRepository('AppBundle:User')->findAll();

        // display the list
        return $this->render('admin/user/list.html.twig', array('users' => $users));
    }
 /**
     * @Route("/message/list", name="admin_message_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function messageListAction(ObjectManager $entityManager)
    {
        $messages = $entityManager->getRepository('AppBundle:Message')->findLatestMessagesReceived();
        return $this->render('admin/message/list.html.twig', array('messages' => $messages));
    }

    /**
     * @Route("/message/{id}/edit", requirements={"id" = "\d+" }, name="admin_message_edit")
     *
     * @param Message $message
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param MessageManager $messageManager
     * @return Response
     */
    public function messageEditAction(Message $message, Request $request, ObjectManager $entityManager,MessageManager $messageManager)
    {
        $origineText = $message->getText() ;
        $messageManager->setModerateText($message);
        
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // RAZ NeedModerate. 
            $message->setNeedModerate(false);
            // The notification will be send to maker or custumer if NeedModerate is false.
            $moderateByAdmin=true;  // No need to notifie admin if the message is steel in moderation.
            $messageManager->sendMessageNotification ($message,$moderateByAdmin);

            $entityManager->flush();
            $this->addFlash('success', 'admin.setting.flash.update');
            if ($request->getSession()->get('referer') != null ) {
                return $this->redirect($request->getSession()->get('referer'));
            } else {
                return $this->redirectToRoute('admin_message_list');
            }

            


        }
  
        $request->getSession()->set('referer', $request->headers->get('referer'));
        return $this->render('admin/message/form.html.twig', array('form' => $form->createView(), 'message' => $message, 'origineText' => $origineText));
    }

    /**
     * @Route("/user/{id}", requirements={"id" = "\d+"}, name="admin_user_see")
     *
     * @param User $user
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function userSeeAction(User $user, Request $request, ObjectManager $entityManager)
    {
        // handle sepa form
        $sepaForm = $this->createForm(UserSepaType::class, $user);
        $sepaForm->handleRequest($request);
        $var ='Salut';
        if ($sepaForm->isSubmitted() && $sepaForm->isValid()) {
            $this->addFlash('success', 'Le client a bien été mis à jour');
            $entityManager->flush();
            return $this->redirectToRoute('admin_user_see', array('id' => $user->getId()));
        }
        return $this->render('admin/user/see.html.twig', array(
            'user' => $user,
            'sepaForm' => $sepaForm->createView(),
            'adminPass' => $var
        ));
    }

    /**
     * @Route("/maker/list", name="admin_maker_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function makerListAction(ObjectManager $entityManager)
    {
        // get all makers
        $makers = $entityManager->getRepository('AppBundle:Maker')->findAll();

        // display the list
        return $this->render('admin/maker/list.html.twig', array('makers' => $makers));
    }

    /**
     * @Route("/maker/{id}", requirements={"id" = "\d+"}, name="admin_maker_see")
     *
     * @param Maker $maker
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param StripeManager $stripeManager
     * @return Response
     */
    public function makerSeeAction(Maker $maker, Request $request, ObjectManager $entityManager, StripeManager $stripeManager)
    {
        // get current commission rate
        $commissionRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_COMMISSION_RATE)->getValue();
        if (null !== $maker->getCustomCommissionRate()) {
            $commissionRate = $maker->getCustomCommissionRate();
        }
        // handle design auto moderation form
        $designAutoModerationForm = $this->createForm(MakerDesignAutoModerationType::class, $maker);
        $designAutoModerationForm->handleRequest($request);
        if ($designAutoModerationForm->isSubmitted() && $designAutoModerationForm->isValid()) {
            $this->addFlash('success', 'La configuration de la modération de ce maker a été mise à jour');
            $entityManager->flush();
            return $this->redirectToRoute('admin_maker_see', array('id' => $maker->getId()));
        }
        // handle commission rate form
        $commissionRateForm = $this->createForm(MakerCommissionRateType::class, $maker);
        $commissionRateForm->handleRequest($request);
        if ($commissionRateForm->isSubmitted() && $commissionRateForm->isValid()) {
            if ($maker->getCustomCommissionRate() !== null && ($maker->getCustomCommissionRate() < 0 || $maker->getCustomCommissionRate() > 100)) {
                $this->addFlash('danger', 'Le taux de commission doit être compris entre 0 et 100');
            } else {
                $this->addFlash('success', 'Le taux de commission a bien été mis à jour');
                $entityManager->flush();
            }
            return $this->redirectToRoute('admin_maker_see', array('id' => $maker->getId()));
        }
        $StripeRequirements = $stripeManager->getRequirements($maker->getStripeId());

        return $this->render('admin/maker/see.html.twig', array(
            'maker' => $maker,
            'currentCommissionRate' => $commissionRate,
            'designAutoModerationForm' => $designAutoModerationForm->createView(),
            'commissionRateForm' => $commissionRateForm->createView(),
            'StripeRequirements' => $StripeRequirements 
        ));
    }

    /**
     * Manage ControlAdmin
     *
     * @Route("/user/{id}/ControlAdmin/{getControle}", requirements={"getControle" = "\d+"}, name="admin_user_controle")
     * @param Maker $user
     * @param int $getControle 
     * @return Response
     */
    public function getControl (User $user , $getControle, ObjectManager $entityManager)
    {

        // update the order status
        if ($getControle == 1) {
            $user->setControlAdmin();
            }
        else
            {
            $user->releaseControlAdmin();
            }
        $entityManager->flush();
        // redirect to the order page
        return $this->redirectToRoute('admin_user_see', array('id' => $user->getId()));
    }

    /**
     * Delete User Account
     *
     * @Route("/user/del/{id}", name="admin_user_delete")
     * @param Maker $user
     * @return Response
     */
    public function deleteUser (User $user , ObjectManager $entityManager, QuotationManager $quotationManager)
    {
        $user->deleteUser();
        if ($user->getMaker() != null) {
            $user->getMaker()->deleteMaker();
            
            $quotations = $entityManager->getRepository('AppBundle:Quotation')->findQuotationForMaker($user->getMaker());
            foreach ($quotations as $quotation) {
                $status = $quotation->getStatus();
                if ( $status ==Quotation::STATUS_PENDING or $status == Quotation::STATUS_PROCESSING or $status == Quotation::STATUS_SENT or $status == Quotation::STATUS_DISPATCHED) {
                    $quotationManager->updateStatus ($quotation, Quotation::STATUS_DISCARDED, QuotationEvent::ORIGIN_ADMIN);
                }
            }  
        }

        $entityManager->flush();
        // redirect to the order page
        return $this->redirectToRoute('admin_user_see', array('id' => $user->getId()));
    }


    /**
     * Manage blacklisting
     *
     * @Route("/maker/{id}/blacklisted/{blacklist}", requirements={"blacklist" = "\d+"}, name="admin_maker_blacklisted")
     * @param Maker $maker
     * @param int $blacklist 
     * @return Response
     */
    public function blacklistMaker (Maker $maker , $blacklist, ObjectManager $entityManager)
    {

        // update the order status
        $maker->setBlacklisted($blacklist);
        $entityManager->flush();
        // redirect to the order page
        return $this->redirectToRoute('admin_maker_see', array('id' => $maker->getId()));
    }
 /**
     * Manage Stripe RelationShip
     *
     * @Route("/maker/{id}/relation/{typeRelation}", requirements={"typeRelation" = "\d+"}, name="admin_maker_stripe_relations")
     * @param Maker $maker
     * @param $typeRelation 
     * @return Response
     */
    public function UpdateStripeRelation (Maker $maker , $typeRelation, StripeManager $stripeManager)
    {
        if ($typeRelation  == 0 ) {
            $stripeManager->updateRelationShip($maker->getStripeId(),'Director');
        }
        if ($typeRelation  == 1 ) {
            $stripeManager->updateRelationShip($maker->getStripeId(),'Executive');
        }
        // redirect to the order page
        return $this->redirectToRoute('admin_maker_see', array('id' => $maker->getId()));
    }
    /**
     * @Route("/maker/{id}/identity-paper/download", name="admin_maker_identity_paper_download")
     *
     * @param Maker $maker
     * @return BinaryFileResponse
     */
    public function makerIdentityPaperDownloadAction(Maker $maker)
    {
        // make sure an attachment exists
        if (null === $maker->getIdentityPaperName()) {
            throw new NotFoundHttpException();
        }

        // get file from upload directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/identy-paper/' . $maker->getIdentityPaperName());

        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $maker->getIdentityPaperName());

        return $response;
    }

    /**
     * @Route("/maker/{id}/identity-paper-verso/download", name="admin_maker_identity_paper_verso_download")
     *
     * @param Maker $maker
     * @return BinaryFileResponse
     */
    public function makerIdentityPaperVersoDownloadAction(Maker $maker)
    {
        // make sure an attachment exists
        if (null === $maker->getIdentityPaperNameVerso()) {
            throw new NotFoundHttpException();
        }

        // get file from upload directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/identy-paper/' . $maker->getIdentityPaperNameVerso());

        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $maker->getIdentityPaperNameVerso());

        return $response;
    }


    /**
     * @Route("/printer/{id}", requirements={"id" = "\d+"}, name="admin_printer_see")
     *
     * @param Printer $printer
     * @return Response
     */
    public function printerSeeAction(Printer $printer)
    {
        return $this->render('admin/printer/see.html.twig', array('printer' => $printer));
    }

    /**
     * @Route("/setting/list", name="admin_setting_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function settingListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/setting/list.html.twig', array('settings' => $entityManager->getRepository('AppBundle:Setting')->findAll()));
    }

    /**
     * @Route("/setting/{id}/edit", requirements={"id" = "\d+"}, name="admin_setting_edit")
     *
     * @param Setting $setting
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function settingEditAction(Setting $setting, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.setting.flash.update');
            return $this->redirectToRoute('admin_setting_list');
        }
        return $this->render('admin/setting/form.html.twig', array('form' => $form->createView(), 'setting' => $setting));
    }

    /**
     * @Route("/coupon/list", name="admin_coupon_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function couponListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/coupon/list.html.twig', array('coupons' => $entityManager->getRepository('AppBundle:Coupon')->findAll()));
    }

    /**
     * @Route("/coupon/add", name="admin_coupon_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function couponAddAction(Request $request, ObjectManager $entityManager)
    {
        $coupon = new Coupon();
        $coupon->setCreatedBy($this->getUser());
        $form = $this->createForm(CouponType::class, $coupon);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($coupon);
            $entityManager->flush();
            $this->addFlash('success', 'admin.coupon.flash.create');
            return $this->redirectToRoute('admin_coupon_list');
        }
        return $this->render('admin/coupon/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/coupon/{id}/edit", requirements={"id" = "\d+"}, name="admin_coupon_edit")
     *
     * @param Coupon $coupon
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function couponEditAction(Coupon $coupon, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(CouponType::class, $coupon);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coupon->setLastModifiedBy($this->getUser());
            $entityManager->flush();
            $this->addFlash('success', 'admin.coupon.flash.update');
            return $this->redirectToRoute('admin_coupon_list');
        }
        return $this->render('admin/coupon/form.html.twig', array('form' => $form->createView(), 'coupon' => $coupon));
    }
    
    /**
     * @Route("/moderation_rule/list", name="admin_moderation_rule_list")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function moderationRuleListAction(Request $request, ObjectManager $entityManager)
    {
       
        return $this->render('admin/moderationRule/list.html.twig', array('moderationRules' => $entityManager->getRepository('AppBundle:ModerationRule')->findAll()));
    }



    /**
     * @Route("/moderation_rule/add", name="admin_moderation_rule_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function moderationRuleAddAction(Request $request, ObjectManager $entityManager)
    {
        $moderationRule = new ModerationRule();
        $moderationRule->setCreatedBy($this->getUser());
        $form = $this->createForm(ModerationRuleType::class, $moderationRule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($moderationRule);
            $entityManager->flush();
            $this->addFlash('success', 'admin.moderation.rule.flash.create');
            return $this->redirectToRoute('admin_moderation_rule_list');
        }
        return $this->render('admin/moderationRule/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/moderation_rule/{id}/edit", requirements={"id" = "\d+"}, name="admin_moderation_rule_edit")
     *
     * @param ModerationRule $moderationRule
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function moderationRuleEditAction(ModerationRule $moderationRule, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(ModerationRuleType::class, $moderationRule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $moderationRule->setLastModifiedBy($this->getUser());
            $entityManager->flush();
            $this->addFlash('success', 'admin.moderation.rule.flash.update');
            return $this->redirectToRoute('admin_moderation_rule_list');
        }
        return $this->render('admin/moderationRule/form.html.twig', array('form' => $form->createView(), 'moderationRule' => $moderationRule));
    }
}