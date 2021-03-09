<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\Quotation;
use AppBundle\Form\QuotationCorrectionType;
use AppBundle\Form\QuotationType;
use AppBundle\Event\QuotationEvent;
use AppBundle\Event\QuotationEvents;
use AppBundle\Service\QuotationManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @Route("/%app.admin_directory%/devis")
 */
class AdminQuotationController extends Controller
{
    /**
     * @Route("/list", name="admin_quotation_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listAction(ObjectManager $entityManager)
    {
        $quotations = $entityManager->getRepository('AppBundle:Quotation')->findQuotationForAdmin();
        return $this->render('admin/quotation/list.html.twig', array('quotations' => $quotations));
    }

    /**
     * @Route("/{reference}", name="admin_quotation_see")
     *
     * @param Quotation $quotation
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param QuotationManager $quotationManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function seeAction(Quotation $quotation, Request $request, ObjectManager $entityManager, QuotationManager $quotationManager, EventDispatcherInterface $eventDispatcher)
    {

        if( $quotation->getStatus() == Quotation::STATUS_SENT or $quotation->getStatus() == Quotation::STATUS_NOT_DISPATCHED) {
            $viewOnly = false;
        } else {
            $viewOnly = true;
        }
        if( $quotation->getStatus() == Quotation::STATUS_SENT) {
            $RefuseQuotation = false;
        } else {
            $RefuseQuotation = true;
        }


        $formQuotation = $this->createForm(QuotationType::class, $quotation,['user_type'=> 'admin','view_only'=>$viewOnly,'refuse_quotation'=>$RefuseQuotation]);
        $formQuotation->handleRequest($request);
        if ($formQuotation->isSubmitted() && $formQuotation->isValid()) {
            $this->addFlash('success', 'maker.project.flash.save.quotation');
            $entityManager->flush();
            if ($formQuotation->get('accept')->isClicked() ) {
                // update the quotation status
                $quotationManager->updateStatus($quotation, Quotation::STATUS_DISPATCHED, QuotationEvent::ORIGIN_ADMIN);
                $this->addFlash('success', 'admin.quotation.flash.accept.dispatch.success');
            }
            if ($formQuotation->get('refuse')->isClicked() ) {
                // update the quotation status
                $quotationManager->updateStatus($quotation, Quotation::STATUS_NOT_DISPATCHED, QuotationEvent::ORIGIN_ADMIN);
                $this->addFlash('success', 'admin.quotation.flash.refuse.dispatch.success');
            }

            if( $quotation->getStatus() == Quotation::STATUS_SENT or $quotation->getStatus() == Quotation::STATUS_NOT_DISPATCHED) {
                $viewOnly = false;
            } else {
                $viewOnly = true;
            }
            if( $quotation->getStatus() == Quotation::STATUS_SENT) {
                $RefuseQuotation = false;
            } else {
                $RefuseQuotation = true;
            }
            $formQuotation = $this->createForm(QuotationType::class, $quotation,['user_type'=> 'admin','view_only'=>$viewOnly,'refuse_quotation'=>$RefuseQuotation]);




        }

        $correctionForm = $this->createForm(QuotationCorrectionType::class, $quotation);
        $correctionForm->handleRequest($request);
        if ($correctionForm->isSubmitted() && $correctionForm->isValid()) {
            // update the quotation status
            $quotationManager->updateStatus($quotation, Quotation::STATUS_PROCESSING, QuotationEvent::ORIGIN_ADMIN);

            // dispatch an event (as update status would not know the correction context)
            $eventDispatcher->dispatch(QuotationEvents::POST_ADMIN_SENT_TO_CORRECTION, new QuotationEvent($quotation, QuotationEvent::ORIGIN_ADMIN));

            $this->addFlash('success', 'admin.quotation.flash.accept.to.correct.success');
        }

        return $this->render('admin/quotation/see.html.twig', array(
                'quotation'      => $quotation,
                'formQuotation'  => $formQuotation->createView(),
                'correctionForm' => $correctionForm->createView()
            )
        );
        
    }

    /**
     * @Route("/{reference}/accept", name="admin_quotation_accept")
     *
     * @param Quotation $quotation
     * @param QuotationManager $quotationManager
     * @return Response
     */
    public function adminAcceptAction(Quotation $quotation, QuotationManager $quotationManager)
    {

        // update the quotation status
        $quotationManager->updateStatus($quotation, Quotation::STATUS_DISPATCHED, QuotationEvent::ORIGIN_ADMIN);

        $this->addFlash('success', 'admin.quotation.flash.accept.dispatch.success');
        
        // display
        return $this->redirectToRoute('admin_quotation_see',array('reference' => $quotation->getReference()));
    }

    /**
     * @Route("/{reference}/refuse", name="admin_quotation_refuse")
     *
     * @param Quotation $quotation
     * @param QuotationManager $quotationManager
     * @return Response
     */
    public function adminRefuseAction(Quotation $quotation, QuotationManager $quotationManager)
    {


        // update the quotation status
        $quotationManager->updateStatus($quotation, Quotation::STATUS_NOT_DISPATCHED, QuotationEvent::ORIGIN_ADMIN);

        $this->addFlash('success', 'admin.quotation.flash.refuse.dispatch.success');
        
        // display
        return $this->redirectToRoute('admin_quotation_see',array('reference' => $quotation->getReference()));
    }






    /**
     * @Route("/message/{id}/attachment/download", name="admin_quotation_message_attachment_download")
     *
     * @param Message $message
     * @return BinaryFileResponse
     */
    public function messageAttachmentDownloadAction(Message $message)
    {
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