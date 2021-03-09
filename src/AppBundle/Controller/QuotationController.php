<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Entity\Maker;
use AppBundle\Entity\Project;
use AppBundle\Entity\Quotation;
use AppBundle\Entity\Setting;
use AppBundle\Event\QuotationEvent;
use AppBundle\Event\QuotationEvents;
use AppBundle\Form\MessageType;
use AppBundle\Service\QuotationManager;
use AppBundle\Event\ProjectEvent;
use AppBundle\Service\ProjectManager;
use AppBundle\Service\SendinBlue;
use AppBundle\Service\MessageManager;
use AppBundle\Form\QuotationType;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class QuotationController extends Controller
{
    /**
     * @Route("/%app.user_directory%/mon-compte-maker/mes-devis", name="quotation_maker_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerListAction(ObjectManager $entityManager)
    {
        $quotations = $entityManager->getRepository('AppBundle:Quotation')->findQuotationForMaker($this->getUser()->getMaker());

        return $this->render('front/user/maker/quotation/list.html.twig', array('quotations' => $quotations));
    }

    /**
     * @Route("/%app.user_directory%/mon-compte-maker/devis/{reference}", name="quotation_maker_see")
     *
     * @param Quotation $quotation
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param QuotationManager $quotationManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerSeeAction(Quotation $quotation, Request $request,ObjectManager $entityManager,QuotationManager $quotationManager)
    {
        if ($this->getUser()->getMaker() !== $quotation->getMaker()) {
            throw new NotFoundHttpException();
        }
        $formQuotation = $this->createForm(QuotationType::class, $quotation);

        $formQuotation->handleRequest($request);

        $delayResponse = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::QUOTATION_AGREEMENT_TIME)->getValue();
        
        if ($formQuotation->isSubmitted() && $formQuotation->isValid()) {

            //If delivery time is negative
            if($quotation->getProductionTime() < 0){

                $this->addFlash('danger', 'maker.project.flash.error.negative.production.time');

                // display
                return $this->render('front/user/maker/quotation/see.html.twig', array(
                    'quotation'       => $quotation,
                    'formQuotation'   => $formQuotation->createView(),
                    'delayResponse' => $delayResponse
                ));

            }

            //If total is negative
            if($quotation->getTotalPrice() < 0){

                $this->addFlash('danger', 'maker.project.flash.error.negative.quotation');

                // display
                return $this->render('front/user/maker/quotation/see.html.twig', array(
                    'quotation'       => $quotation,
                    'formQuotation'   => $formQuotation->createView(),
                    'delayResponse' => $delayResponse
                ));

            }

            $saveOrSent = $formQuotation->get('save')->isClicked() ? 'save' : 'sent';

            if($saveOrSent == 'save'){

                $this->addFlash('success', 'maker.project.flash.save.quotation'); 

            } else if ($saveOrSent == 'sent'){

                $this->addFlash('success', 'maker.project.flash.sent.quotation');
                if ($quotation->getMaker()->getDesignAutoModeration()) {
                    // if auto moderation is on, directly set the quotation as dispatched
                    $quotationManager->updateStatus($quotation, Quotation::STATUS_DISPATCHED, QuotationEvent::ORIGIN_MAKER);
                } else {
                    // else set it to sent (admin will have to moderate manually)
                    $quotationManager->updateStatus($quotation, Quotation::STATUS_SENT, QuotationEvent::ORIGIN_MAKER);
                }
            }

            // update savedAt date
            $quotation->setSavedAt(new \DateTime('now', new \DateTimeZone('UTC')));

            $entityManager->flush();

        }

        // create the message form, only if quotation status allows it
        $messageFormView = null;
        if (in_array($quotation->getStatus(), array(Quotation::STATUS_DISPATCHED))) {
            $messageForm = $this->createForm(MessageType::class, new Message(), array(
                'action' => $this->generateUrl('quotation_message_send', array('reference' => $quotation->getReference(), 'from' => 'maker'))
            ));
            $messageFormView = $messageForm->createView();
        }
        
        // display
        return $this->render('front/user/maker/quotation/see.html.twig', array(
            'quotation'     => $quotation,
            'formQuotation' => $formQuotation->createView(),
            'messageForm'   => $messageFormView,
            'delayResponse' => $delayResponse
        ));
    }

    /**
     * @Route("/%app.user_directory%/mon-compte-maker/devis/{reference}/delete", name="quotation_maker_delete")
     *
     * @param Quotation $quotation
     * @param QuotationManager $quotationManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerDeleteAction(Quotation $quotation, QuotationManager $quotationManager)
    {
        if ($this->getUser()->getMaker() !== $quotation->getMaker()) {
            throw new NotFoundHttpException();
        }

        // update the quotation status
        $quotationManager->updateStatus($quotation, Quotation::STATUS_REFUSED, QuotationEvent::ORIGIN_MAKER);

        $this->addFlash('success', 'maker.project.flash.delete.quotation.success');
        
        // display
        return $this->redirectToRoute('quotation_maker_list');
    }

    /**
     * @Route("/%app.user_directory%/mon-compte-maker/devis/{reference}/accept", name="quotation_maker_accept")
     *
     * @param Quotation $quotation
     * @param QuotationManager $quotationManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerAcceptAction(Quotation $quotation, QuotationManager $quotationManager)
    {
        if ($this->getUser()->getMaker() !== $quotation->getMaker()) {
            throw new NotFoundHttpException();
        }

        // update the quotation status
        $quotationManager->updateStatus($quotation, Quotation::STATUS_PROCESSING, QuotationEvent::ORIGIN_MAKER);

        //$this->addFlash('success', 'maker.project.flash.accept.quotation.success');
        
        // display
        return $this->redirectToRoute('quotation_maker_see',array('reference' => $quotation->getReference()));
    }

    /**
     * @Route("/%app.user_directory%/mon-compte-client/devis/{reference}", name="quotation_customer_see")
     *
     * @param Quotation $quotation
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerSeeAction(Quotation $quotation)
    {
        
        if ($this->getUser() !== $quotation->getProject()->getCustomer()) {
            throw new NotFoundHttpException();
        }

        // create the message form, only if quotation status allows it
        $messageFormView = null;
        if (in_array($quotation->getStatus(), array(Quotation::STATUS_DISPATCHED))) {
            $messageForm = $this->createForm(MessageType::class, new Message(), array(
                'action' => $this->generateUrl('quotation_message_send', array('reference' => $quotation->getReference(), 'from' => 'customer'))
            ));
            $messageFormView = $messageForm->createView();
        }
        
        // display
        return $this->render('front/user/quotation/see.html.twig', array(
            'quotation'   => $quotation,
            'messageForm' => $messageFormView
        ));
    }

    /**
     * Download the quotation
     *
     * @Route("/quotation/{reference}/pdf", name="quotation_download")
     *
     * @param Quotation $quotation
     * @param ObjectManager $entityManager
     * @return PdfResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function quotationDownloadAction(Quotation $quotation,ObjectManager $entityManager)
    {
        // make sure the user is allowed to download this quotation
        if (($quotation->getProject()->getCustomer() !== $this->getUser()) && ($quotation->getMaker()->getUser() !== $this->getUser())) {
            throw new AccessDeniedException();
        }

        //$settingMaxDayResponse = "+".$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::QUOTATION_AGREEMENT_TIME)->getValue()." day";

        $addDayClosed = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::QUOTATION_AGREEMENT_TIME)->getValue().'D';

        $quotationValidity = $quotation->getProject()->getClosedAt()->add(new \DateInterval($addDayClosed));

        $html = $this->renderView('front/pdf/quotation.html.twig', array(
            'quotation'=> $quotation,'quotationValidity' => date_format($quotationValidity, 'd/m/Y')
        ));

        $footer = $this->renderView('front/pdf/footer.html.twig');

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'footer-html' => $footer,
                'margin-right'  => 13,
                'margin-bottom' => 55,
                'margin-left'   => 13
            )),
            'U3DM-DEVIS-'.$quotation->getReference().'.pdf'
        );
    }

    /**
     * @Route("/quotation/{reference}/message/{from}/send", name="quotation_message_send")
     *
     * @param Quotation $quotation
     * @param string $from
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param MessageManager $messageManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function messageSendAction(Quotation $quotation, $from, Request $request, ObjectManager $entityManager, MessageManager $messageManager)
    {
        // security checks
        if (('customer' === $from && $this->getUser() !== $quotation->getProject()->getCustomer()) || ('maker' === $from && $this->getUser()->getMaker() !== $quotation->getMaker())) {
            throw new AccessDeniedException();
        }

        // message form handling
        $message = new Message();
        $messageForm = $this->createForm(MessageType::class, $message);
        $messageForm->handleRequest($request);
        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $message->setQuotation($quotation);
            $message->setAuthor($this->getUser());
            $message->setAuthorMaker(false);
            if ('maker' === $from) {
                $message->setAuthorMaker(true);
            }
            $entityManager->persist($message);
            $entityManager->flush();
            $messageManager->sendMessageNotification ($message);
            $entityManager->persist($message);
            $entityManager->flush();

           // Send Notification to the maker or client or admin, if message need or not to be moderate


            $this->addFlash('success', 'Votre message a été envoyé.');
        }

        $redirectRoute = $this->generateUrl('quotation_customer_see', array('reference' => $quotation->getReference()));
        if ('maker' === $from) {
            $redirectRoute = $this->generateUrl('quotation_maker_see', array('reference' => $quotation->getReference()));
        }
        return new RedirectResponse($redirectRoute);
    }

    /**
     * @Route("/quotation/message/{id}/attachment/download", name="quotation_message_attachment_download")
     *
     * @param Message $message
     * @return BinaryFileResponse
     */
    public function messageAttachmentDownloadAction(Message $message)
    {
        // check the user is allowed to see this quotation
        $quotation = $message->getQuotation();
        if (null === $this->getUser()) {
            throw new AccessDeniedException();
        }
        if ($this->getUser() instanceof User) {
            if ($this->getUser() !== $quotation->getProject()->getCustomer() && $this->getUser()->getMaker() !== $quotation->getMaker()) {
                throw new AccessDeniedException();
            }
        }

        // make sure an attachment exists
        if (null === $message->getAttachmentName()) {
            throw new NotFoundHttpException();
        }

        // get file from project directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/message/attachment/' . $message->getAttachmentName());

        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $message->getAttachmentOriginalName());
        return $response;
    }
}