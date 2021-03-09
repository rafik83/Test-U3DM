<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\Rating;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderFile;
use AppBundle\Entity\Shipping;
use AppBundle\Entity\Shipment;
use AppBundle\Form\ShipmentType;
use AppBundle\Entity\User;
use AppBundle\Event\OrderEvent;
use AppBundle\Form\MessageOrderFileRejectType;
use AppBundle\Form\MessageType;
use AppBundle\Form\RatingType;
use AppBundle\Form\OrderFileType;
use AppBundle\Service\OrderManager;
use AppBundle\Service\MessageManager;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use AppBundle\Service\StripeManager;
use Psr\Log\LoggerInterface;

class OrderController extends Controller
{
    private $logger;

    public function __construct(  LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @Route("/%app.user_directory%/mon-compte-client/mes-commandes", name="order_customer_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerListAction(ObjectManager $entityManager)
    {
        $orders = $entityManager->getRepository('AppBundle:Order')->findOrdersForCustomer($this->getUser());
        return $this->render('front/user/order/list.html.twig', array('orders' => $orders));
    }

    /**
     * @Route("/%app.user_directory%/mon-compte-client/commande/{reference}", name="order_customer_see")
     *
     * @param Order $order
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerSeeAction(Order $order, ObjectManager $entityManager)
    {
        if ($this->getUser() !== $order->getCustomer()) {
            throw new NotFoundHttpException();
        }
        // define the reject form if the order contains a file to download and is awaiting validation
        $rejectFormView = null;
        if (null !== $order->getFile() && in_array($order->getStatus(), array(Order::STATUS_FILE_AVAILABLE, Order::STATUS_FILE_DOWNLOADED))) {
            $rejectForm = $this->createForm(MessageOrderFileRejectType::class, new Message(), array(
                'action' => $this->generateUrl('order_file_validate', array('id' => $order->getFile()->getId()))
                ));
            $rejectFormView = $rejectForm->createView();
        }
        // flag to tell if we want to show the shipment on order page
        $showShipment = false;
        if ($order->getShippingType() !== Shipping::TYPE_PICKUP && in_array($order->getStatus(), array(Order::STATUS_TRANSIT, Order::STATUS_DELIVERED, Order::STATUS_PND, Order::STATUS_CLOSED))) {
            $showShipment = true;
        }
        // flag to tell if we want to show the invoice download link
        $showInvoice = false;
        if (in_array($order->getStatus(), array(Order::STATUS_TRANSIT, Order::STATUS_DELIVERED, Order::STATUS_PND, Order::STATUS_CLOSED, Order::STATUS_MODEL_PAID))) {
            $showInvoice = true;
        }
        // create the message form, only if order status allows it
        $messageFormView = null;
        if (!in_array($order->getStatus(), array(Order::STATUS_CANCELED , Order::STATUS_REFUNDED, Order::STATUS_AWAITING_SEPA, Order::STATUS_REFUSED_SEPA, Order::STATUS_TRANSIT, Order::STATUS_DELIVERED, Order::STATUS_PND, Order::STATUS_CLOSED))) {
            $messageForm = $this->createForm(MessageType::class, new Message(), array(
                'action' => $this->generateUrl('order_message_send', array('reference' => $order->getReference(), 'from' => 'customer'))
            ));
            $messageFormView = $messageForm->createView();
        }
        // create the rating form, only if order status allows it
        $ratingFormView = null;
        //$ratingRate = null;
        //$ratingComment = null;
        $ratingSave='Message.order.unknown';
        if ($order->getStatus() == Order::STATUS_DELIVERED && null === $order->getRating()) {
            $ratingSave='Message.order.rating.wait';
            $ratingForm = $this->createForm(RatingType::class, new Rating(), array(
                'action' => $this->generateUrl('order_rating_save', array('reference' => $order->getReference()))
            ));
            $ratingFormView = $ratingForm->createView();

        } else if(null != $order->getRating() ){
            $ratingSave='Message.order.rating.done';
        }

        $models = null;
        
        if ($order->getType() === 'model') {
            $models = $entityManager->getRepository('AppBundle:Model')->findModelFromOrder($order);
        }

        // display
        return $this->render('front/user/order/see.html.twig', array(
            'order'        => $order,
            'rejectForm'   => $rejectFormView,
            'messageForm'  => $messageFormView,
            'showShipment' => $showShipment,
            'showInvoice'  => $showInvoice,
            'ratingForm'   => $ratingFormView,
            //'ratingRate'   => $ratingRate,
            'models'   => $models,
            //'ratingComment'   => $ratingComment,
            'ratingSave'   => $ratingSave,
            'isMaker' => false
        ));
    }

    /**
     * @Route("/rating/{token}", name="order_rating")
     *
     * @param string $token
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return RedirectResponse
     */
    public function ratingOrder ($token,  Request $request, ObjectManager $entityManager, OrderManager $orderManager)
    {
        // security checks
         // look for an user with this reset token
        /** @var Order $order */
        $ratingSave='Message.order.unknown';
        $rating = new Rating();
        $ratingForm = $this->createForm(RatingType::class, $rating);

        $order = $entityManager->getRepository('AppBundle:Order')->findOneByToken($token);
        if ($order != null) {

            if ($order->getStatus() == Order::STATUS_DELIVERED && null === $order->getRating()) {

                $ratingSave='Message.order.rating.wait';
                $ratingForm->handleRequest($request);
        
                if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {

                    
                    $rating->setOrder($order);
                    $rating->setCustomer($order->getCustomer());
                    $rating->setMaker($order->getMaker());
                    $rating->setEnabled(false);
        
                    $order->setRating($rating);
        
                    $entityManager->persist($rating,$order);
                    $entityManager->flush();
        
                    //$this->addFlash('success', 'Votre avis a bien été enregisté.');
        
                    $orderManager->updateStatus($order, Order::STATUS_CLOSED, OrderEvent::ORIGIN_CUSTOMER);
                    $ratingSave='Message.order.rating.thanks';
                


                }
            } elseif (null != $order->getRating()){
                $ratingSave='Message.order.rating.done';
            } 
        }
        


        return $this->render('front/user/rating_with_token.html.twig', array(
            'order' => $order,
            'ratingForm' => $ratingForm->createView(),
            'ratingSave' => $ratingSave))  ;
    }


/**
     * @Route("/%app.user_directory%/mon-compte-client/rating/{reference}", name="order_customer_rate")
     *
     * @param Order $order
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerRateAction(Order $order, ObjectManager $entityManager)
    {
        if ($this->getUser() !== $order->getCustomer()) {
            throw new NotFoundHttpException();
        }
    
        // create the rating form, only if order status allows it
        $ratingFormView = null;
        $ratingSave='Message.order.unknown';

        if ($order->getStatus() == Order::STATUS_DELIVERED && null === $order->getRating()) {
            $ratingSave='Message.order.rating.wait';
            $ratingForm = $this->createForm(RatingType::class, new Rating(), array(
                'action' => $this->generateUrl('order_rating_save', array('reference' => $order->getReference()))
            ));
            $ratingFormView = $ratingForm->createView();

        } elseif (null != $order->getRating()){
            $ratingSave='Message.order.rating.done';
        } 

        $models = null;
        
        if ($order->getType() === 'model') {
            $models = $entityManager->getRepository('AppBundle:Model')->findModelFromOrder($order);
        }

        // display
        return $this->render('front/user/rating/see.html.twig', array(
            'order'        => $order,
            'ratingForm'   => $ratingFormView,
            'ratingSave'   => $ratingSave,
            'isMaker' => false
        ));
    }





    /**
     * @Route("/%app.user_directory%/mon-compte-maker/mes-commandes", name="order_maker_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerListAction(ObjectManager $entityManager)
    {
        $orders = $entityManager->getRepository('AppBundle:Order')->findOrdersForMaker($this->getUser()->getMaker());
        return $this->render('front/user/maker/order/list.html.twig', array('orders' => $orders));
    }

    /**
     * @Route("/%app.user_directory%/mon-compte-maker/commande/{reference}", name="order_maker_see")
     *
     * @param Order $order
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerSeeAction(Order $order, ObjectManager $entityManager)
    {

        if ($this->getUser()->getMaker() !== $order->getMaker()) {
            throw new NotFoundHttpException();
        }
        // create the message form, only if order status allows it
        $messageFormView = null;
        if (!in_array($order->getStatus(), array(Order::STATUS_CANCELED , Order::STATUS_REFUNDED, Order::STATUS_AWAITING_SEPA, Order::STATUS_REFUSED_SEPA, Order::STATUS_TRANSIT, Order::STATUS_DELIVERED, Order::STATUS_PND, Order::STATUS_CLOSED))) {
 
            $messageForm = $this->createForm(MessageType::class, new Message(), array(
                'action' => $this->generateUrl('order_message_send', array('reference' => $order->getReference(), 'from' => 'maker'))
            ));
            $messageFormView = $messageForm->createView();
        }

        // create the tracking form, only if order status allows it
        $shipmentFormView = null;
        if ($order->getType() == Order::TYPE_DESIGN) {
            //
            $ProjectType = $order->getQuotation()->getProject()->getType();
            if ( in_array($order->getStatus(), array(Order::STATUS_PROCESSING, Order::STATUS_FILE_VALIDATED)) and ($ProjectType->isShipping()) and ($ProjectType->isShippingChoice()== false) ) {
                
                
                $shipmentForm = $this->createForm(ShipmentType::class, new Shipment(), array(
                    'action' => $this->generateUrl('order_shipment_save', array('reference' => $order->getReference()))
                ));
                $shipmentFormView = $shipmentForm->createView();
            }
        }
        $fileFormView = null;
        $shouldHaveOrderFile = false;
        if (null !== $order->getQuotation()) {
            $shouldHaveOrderFile = $order->getQuotation()->getProject()->getType()->isFile();
        }
        //Order file
        //HISTO :if (($order->getStatus() == Order::STATUS_PROCESSING || $order->getStatus() == Order::STATUS_FILE_REJECTED) && $order->getType() == Order::TYPE_DESIGN && $shouldHaveOrderFile ) {
        if (($order->getStatus() == Order::STATUS_PROCESSING &&  $shouldHaveOrderFile ) || ($order->getStatus() == Order::STATUS_FILE_REJECTED)  ) {

            $fileForm = $this->createForm(OrderFileType::class, new OrderFile(), array(
                'action' => $this->generateUrl('order_file_save', array('reference' => $order->getReference()))
            ));
            $fileFormView = $fileForm->createView();

        }
        $models = null;
        if ($order->getType() === 'model') {
            $models = $entityManager->getRepository('AppBundle:Model')->findModelFromOrder($order);
        }

        // display
        return $this->render('front/user/maker/order/see.html.twig', array(
            'order'       => $order,
            'messageForm' => $messageFormView,
            'fileForm'    => $fileFormView,
            'shipmentForm'    => $shipmentFormView,
            'models'    => $models,
            'nbItems' => count($order->getItems() ),
            'isMaker' => true
        ));
    }

    /**
     * @Route("/order/{reference}/generateLabel", name="order_generate_label")
     *
     * @param Order $order
     * @param OrderManager $orderManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function generateLabelAction(Order $order, OrderManager $orderManager)
    {

        // make sure the maker can edit this order
        if ($order->getMaker() !== $this->getUser()->getMaker()) {
            throw new NotFoundHttpException();
        }
        
        // we can only generate a label if order status is processing or labeled, or if it is a design order with a validated file status
        if ($order->getStatus() !== Order::STATUS_PROCESSING && $order->getStatus() !== Order::STATUS_LABELED && (Order::TYPE_DESIGN !== $order->getType() || $order->getStatus() !== Order::STATUS_FILE_VALIDATED)) {
            throw new AccessDeniedException();
        }

        // generate label
        try {

            $orderManager->generateLabel($order);

        } catch(\Exception $exception) {

            $this->addFlash('danger', 'Une erreur est survenue lors de la création de l\'étiquette, veuillez ré-essayer ou contacter le support technique.');

        }

        return $this->redirectToRoute('order_maker_see', array('reference' => $order->getReference()));

    }

    /**
     * Manage updates, done by the maker
     *
     * @Route("/order/{reference}/status/{newStatus}/update", requirements={"newStatus" = "\d+"}, name="order_maker_status_update")
     *
     * @param Order $order
     * @param int $newStatus : as defined in Order class STATUS_* constants
     * @param OrderManager $orderManager
     * @return Response
     * @Security("has_role('ROLE_MAKER')")
     */
    public function makerStatusUpdateAction(Order $order, $newStatus, OrderManager $orderManager)
    {
        // make sure the maker can edit this order
        if ($order->getMaker() !== $this->getUser()->getMaker()) {
            throw new NotFoundHttpException();
        }

        // make sure the status update is legit
        $currentStatus = $order->getStatus();
        $allowedStatuses = array();
        switch ($currentStatus) {
            case Order::STATUS_NEW:
                $allowedStatuses = array(Order::STATUS_PROCESSING);
                break;
            case Order::STATUS_PROCESSING:
                $allowedStatuses = array(Order::STATUS_LABELED, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_DELIVERED);
                break;
            case Order::STATUS_LABELED:
                $allowedStatuses = array(Order::STATUS_LABELED);
                break;
            case Order::STATUS_READY_FOR_PICKUP:
                $allowedStatuses = array(Order::STATUS_DELIVERED);
                break;
            case Order::STATUS_FILE_VALIDATED:
                    $allowedStatuses = array(Order::STATUS_READY_FOR_PICKUP);
                break;
        }
        if (!in_array($newStatus, $allowedStatuses)) {
            throw new AccessDeniedException();
        }

        // update the order status
        $orderManager->updateStatus($order, $newStatus, OrderEvent::ORIGIN_MAKER);

        // redirect to the order page
        return $this->redirectToRoute('order_maker_see', array('reference' => $order->getReference()));
    }

    /**
     * Cancel an order, done by the customer
     *
     * @Route("/order/{reference}/cancel", name="order_customer_cancel")
     *
     * @param Order $order
     * @param OrderManager $orderManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerCancelAction(Order $order, OrderManager $orderManager)
    {
        // make sure the user can edit this order
        if ($order->getCustomer() !== $this->getUser()) {
            throw new NotFoundHttpException();
        }

        // customer can only cancel an order that is still new
        if ($order->getStatus() !== Order::STATUS_NEW) {
            throw new AccessDeniedException();
        }

        // update the order status
        $orderManager->updateStatus($order, Order::STATUS_CANCELED, OrderEvent::ORIGIN_CUSTOMER);

        // redirect to the order page
        return $this->redirectToRoute('order_customer_see', array('reference' => $order->getReference()));
    }

    /**
     * Track orders that should be tracked, and perform the appropriate actions accordingly.
     * Meant to be called in a cron task.
     *
     * @Route("/order/track", name="order_track")
     *
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return Response
     */
    public function trackAction(ObjectManager $entityManager, OrderManager $orderManager)
    {
        // get all orders that should be tracked
        $orders = $entityManager->getRepository('AppBundle:Order')->findOrdersToTrack();
        foreach ($orders as $order) {
            $orderManager->trackShipmentAndUpdateOrder($order);
        }
        return new Response();
    }


    /**
     * followup customer who didn't rate the order
     * Meant to be called in a cron task.
     *
     * @Route("/order/follow", name="order_follow")
     *
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return Response
     */
    public function followAction(ObjectManager $entityManager, OrderManager $orderManager)
    {

        // get all orders that should be rating
        $orderManager->CustomerFollowUpRating();



        return new Response();
    }




    /**
     * Download the order invoice
     *
     * @Route("/order/{reference}/invoice", name="order_invoice_download")
     *
     * @param Order $order
     * @return PdfResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function invoiceDownloadAction(Order $order, ObjectManager $entityManager)
    {
        // make sure the user is allowed to download this invoice
        if ($order->getCustomer()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        // set the invoice number
        $invoiceNumber  = $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('ym');
        $invoiceNumber .= str_pad($order->getId(), 4, '0', STR_PAD_LEFT);

        $models = null;
        
        if ($order->getType() === 'model') {
            $models = $entityManager->getRepository('AppBundle:Model')->findModelFromOrder($order);
        }

        $html = $this->renderView('front/pdf/invoice.html.twig', array(
            'invoiceNumber'  => $invoiceNumber,
            'order'          => $order,
            'models'   => $models
        ));

        $footer = $this->renderView('front/pdf/footer.html.twig');

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'footer-html' => $footer,
                'margin-right'  => 13,
                'margin-bottom' => 55,
                'margin-left'   => 13
            )),
            'U3DM-'.$invoiceNumber.'.pdf'
        );
    }

    /**
     * Download the order delivery slip
     *
     * @Route("/order/{reference}/deliverySlip", name="order_delivery_slip_download")
     *
     * @param Order $order
     * @return PdfResponse
     * @Security("has_role('ROLE_MAKER')")
     */
    public function deliverySlipDownloadAction(Order $order)
    {
        // make sure the maker is allowed to download this delivery slip
        if ($order->getMaker()->getId() !== $this->getUser()->getMaker()->getId()) {
            throw new AccessDeniedException();
        }

        // look for the shipment
        $shipment = null;
        if (0 < count($order->getShipments())) {
            $shipment = $order->getShipments()->first();
        }

        $html = $this->renderView('front/pdf/delivery_slip.html.twig', array(
            'order'    => $order,
            'shipment' => $shipment
        ));


        $footer = $this->renderView('front/pdf/footer.html.twig');

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'footer-html' => $footer,
                'margin-right'  => 13,
                'margin-bottom' => 55,
                'margin-left'   => 13
            )),
            'U3DM-BL-'.$order->getReference().'.pdf'
        );
    }

    /**
     * @Route("/order/{reference}/message/{from}/send", name="order_message_send")
     *
     * @param Order $order
     * @param string $from
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param MessageManager $messageManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function messageSendAction(Order $order, $from, Request $request, ObjectManager $entityManager, MessageManager $messageManager)
    {
        // security checks
        if (('customer' === $from && $this->getUser() !== $order->getCustomer()) || ('maker' === $from && $this->getUser()->getMaker() !== $order->getMaker())) {
            throw new AccessDeniedException();
        }

        // message form handling
        $message = new Message();
        $messageForm = $this->createForm(MessageType::class, $message);
        $messageForm->handleRequest($request);
        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $message->setOrder($order);
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

        $redirectRoute = $this->generateUrl('order_customer_see', array('reference' => $order->getReference()));
        if ('maker' === $from) {
            $redirectRoute = $this->generateUrl('order_maker_see', array('reference' => $order->getReference()));
        }
        return new RedirectResponse($redirectRoute);
    }




    /**
     * @Route("/order/{reference}/rating/send", name="order_rating_save")
     *
     * @param Order $order
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function ratingSaveAction(Order $order, Request $request, ObjectManager $entityManager,OrderManager $orderManager)
    {
        // security checks
        if ($this->getUser() !== $order->getCustomer()) {
            throw new AccessDeniedException();
        }

        // message form handling
        $rating = new Rating();
        $ratingForm = $this->createForm(RatingType::class, $rating);
        $ratingForm->handleRequest($request);

        if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {

            $rating->setOrder($order);
            $rating->setCustomer($this->getUser());
            $rating->setMaker($order->getMaker());
            $rating->setEnabled(false);

            $order->setRating($rating);

            $entityManager->persist($rating,$order);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a bien été enregisté.');

            $orderManager->updateStatus($order, Order::STATUS_CLOSED, OrderEvent::ORIGIN_CUSTOMER);



        } else {

            $this->addFlash('danger', 'Message.order.no_rate');

        }


        $redirectRoute = $this->generateUrl('order_customer_see', array('reference' => $order->getReference()));
        
        return new RedirectResponse($redirectRoute);
    }
    
    /**
     * @Route("/order/{reference}/shipment/send", name="order_shipment_save")
     *
     * @param Order $order
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function shipmentSaveAction(Order $order, Request $request, ObjectManager $entityManager, OrderManager $orderManager)
    {
        // make sure the maker can edit this order
        if ($order->getMaker() !== $this->getUser()->getMaker()) {
            throw new NotFoundHttpException();
        }

        // generate label
        try {
            // message form handling
            $shipment = new shipment();
            $shipmentForm = $this->createForm(ShipmentType::class, $shipment);
            $shipmentForm->handleRequest($request);

            if ($shipmentForm->isSubmitted() && $shipmentForm->isValid()) {
               
                $shipment->setOrder($order);
                $shipment->setType(Shipment::TYPE_AUTRE);
                $shipment->setLabelPdfUrl("None");
                $order->addShipment($shipment);

                $entityManager->persist($shipment,$order);
                $entityManager->flush();

                // update order status
                $orderManager->updateStatus($order, Order::STATUS_LABELED,OrderEvent::ORIGIN_MAKER);
                $this->addFlash('success', 'Votre avis a bien été enregisté.');

            } else {

                $this->addFlash('danger', 'Erreur lors de l\'enregistrement');

            }


        } catch(\Exception $exception) {

            $this->addFlash('danger', 'Une erreur est survenue lors de la création de l\'étiquette, veuillez ré-essayer ou contacter le support technique.');

        }
















        $redirectRoute = $this->generateUrl('order_maker_see', array('reference' => $order->getReference()));
        
        return new RedirectResponse($redirectRoute);
    }


    /**
     * @Route("/order/message/{id}/attachment/download", name="order_message_attachment_download")
     *
     * @param Message $message
     * @return BinaryFileResponse
     */
    public function messageAttachmentDownloadAction(Message $message)
    {
        // check the user is allowed to see this order
        $order = $message->getOrder();
        if (null === $this->getUser()) {
            throw new AccessDeniedException();
        }
        if ($this->getUser() instanceof User) {
            if ($this->getUser() !== $order->getCustomer() && $this->getUser()->getMaker() !== $order->getMaker()) {
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

    /**
     * @Route("/order/{reference}/file/send", name="order_file_save")
     *
     * @param Order $order
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function fileSaveAction(Order $order, Request $request, ObjectManager $entityManager, OrderManager $orderManager)
    {
        // security checks
        if ($this->getUser() !== $order->getMaker()->getUser()) {
            throw new AccessDeniedException();
        }

        // message form handling
        $file = new OrderFile();
        $fileForm = $this->createForm(OrderFileType::class, $file);
        $fileForm->handleRequest($request);
        $fileName = '' ;
        $originalName = '';
        $url='';
        if ($fileForm->isSubmitted() && $fileForm->isValid()) {

            //File process
            
            $file = $fileForm['file']->getData();
            //$url = $fileForm['urlDownload']->getData();
            if (($file <> null) or ($url <> null and substr($url,0,4)=="http")) {

                if ($file <> null) {
                        $fileName = md5 ( uniqid () ) . '.' . $file->guessExtension ();
                        $originalName = $file->getClientOriginalName ();
                        $file->move($this->get('kernel')->getRootDir().'/../var/uploads/order',$fileName);
                    }
                    if($order->getFile()){

                        $newFile = $order->getFile();

                    } else {

                        $newFile = new OrderFile();
                    }
                    
                    $newFile->setName($fileName);
                    $newFile->setOrder($order);
                    $newFile->setOriginalName($originalName);
                    //$newFile->setUrlDownload($url);
                    $order->setFile($newFile);

                    $entityManager->persist($newFile,$order);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre fichier a bien été enregistré.');

                    $orderManager->updateStatus($order, Order::STATUS_FILE_AVAILABLE, OrderEvent::ORIGIN_MAKER);
                } else {
                $this->addFlash('danger', 'Déposer un fichier ou renseigner un lien de téléchargement valide.');
            }


        } else {

            $this->addFlash('danger', 'Erreur lors de l\'enregistrement');

        }


        $redirectRoute = $this->generateUrl('order_maker_see', array('reference' => $order->getReference()));
        
        return new RedirectResponse($redirectRoute);
    }

    /**
     * @Route("/order/file/{id}/download", name="order_file_download")
     *
     * @param OrderFile $orderFile
     * @param OrderManager $orderManager
     * @return BinaryFileResponse   
     */
    public function orderFileDownloadAction (OrderFile $orderFile, OrderManager $orderManager)
    {
        // check the user is allowed to see this order
        $order = $orderFile->getOrder();

        if (null === $this->getUser()) {
            throw new AccessDeniedException();
        }
        if ($this->getUser() instanceof User) {
            if ($this->getUser() !== $order->getCustomer() && $this->getUser()->getMaker() !== $order->getMaker()) {
                throw new AccessDeniedException();
            }
        }

        //Change Status if => getUser = customer AND order status = STATUS_FILE_AVAILABLE
        if($order->getStatus() == Order::STATUS_FILE_AVAILABLE && $this->getUser() == $order->getCustomer()){

            $orderManager->updateStatus($order, Order::STATUS_FILE_DOWNLOADED, OrderEvent::ORIGIN_CUSTOMER);
        }

        // make sure an attachment exists
        if (null === $orderFile) {
            throw new NotFoundHttpException();
        }
        if ( $orderFile->getUrlDownload() != null ) {
            $response = new RedirectResponse($orderFile->getUrlDownload());

        }else {
            // get file from project directory
            $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/order/' . $orderFile->getName());
            
            // prevent caching
            $response->setPrivate();
            $response->setMaxAge(0);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->headers->addCacheControlDirective('no-store', true);

            // force file download
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $orderFile->getOriginalName());
        }
        return $response;
    }

    /**
     * @Route("/order/file/{id}/validate", name="order_file_validate")
     *
     * @param Request $request
     * @param OrderFile $orderFile
     * @param OrderManager $orderManager
     * @param ObjectManager $entityManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function orderFileAcceptAction(Request $request, OrderFile $orderFile, OrderManager $orderManager, ObjectManager $entityManager, MessageManager $messageManager)
    {
        // check the user is allowed to see this order
        $order = $orderFile->getOrder();
        if ($this->getUser() !== $order->getCustomer()) {
            throw new AccessDeniedException();
        }

        // check the action is legit
        if (!in_array($order->getStatus(), array(Order::STATUS_FILE_AVAILABLE, Order::STATUS_FILE_DOWNLOADED))) {
            throw new AccessDeniedException();
        }

        // message form handling
        $message = new Message();
        $messageForm = $this->createForm(MessageOrderFileRejectType::class, $message);
        $messageForm->handleRequest($request);
        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $message->setOrder($order);
            $message->setAuthor($this->getUser());
            $message->setAuthorMaker(false);

            if ($messageForm->get('validate')->isClicked()){
                if ($message->getText() <> "") {
                    $message->setText('Le livrable a été accepté.' .chr(13) . $message->getText());
                }else {
                    $message->setText('Le livrable a été accepté.');
                }

                // Set if the message need to be moderate
                $messageManager->setNeedModerateText($message);
                if ($message->isNeedModerate() == false) {
                    // apply moderation, because it's can apply the automatic moderation
                    $messageManager->setModerateText($message);
                } else {
                    // Send notification to admin that a message need to be moderate
                    $entityManager->persist($message);
                    $entityManager->flush();
                    $messageManager->sendMessageNotification($message);
                }
                // update order status
                $orderManager->updateStatus($order, Order::STATUS_FILE_VALIDATED, OrderEvent::ORIGIN_CUSTOMER);
            } else {
                $message->setText('Le livrable a été refusé.' .chr(13) . $message->getText());
                 // update order status and Force the message to be moderate
                
                $orderManager->updateStatus($order, Order::STATUS_FILE_MODERATE_REJECTED, OrderEvent::ORIGIN_CUSTOMER);
                $message->setNeedModerate(true);
                $entityManager->persist($message);
                $entityManager->flush();
                $messageManager->sendMessageNotification($message);
            }

            $entityManager->persist($message);
            $entityManager->flush();
        }

        // update order status
        //$orderManager->updateStatus($order, Order::STATUS_FILE_VALIDATED, OrderEvent::ORIGIN_CUSTOMER);

        // redirect
        return new RedirectResponse($this->generateUrl('order_customer_see', array('reference' => $order->getReference())));
    }




  
    /**
     * @Route("/order/file/{id}/reject", name="order_file_reject")
     *
     * @param Request $request
     * @param OrderFile $orderFile
     * @param OrderManager $orderManager
     * @param ObjectManager $entityManager
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function orderFileRejectAction(Request $request, OrderFile $orderFile, OrderManager $orderManager, ObjectManager $entityManager)
    {
        // check the user is allowed to see this order
        $order = $orderFile->getOrder();
        if ($this->getUser() !== $order->getCustomer()) {
            throw new AccessDeniedException();
        }

        // check the action is legit
        if (!in_array($order->getStatus(), array(Order::STATUS_FILE_AVAILABLE, Order::STATUS_FILE_DOWNLOADED))) {
            throw new AccessDeniedException();
        }

        // message form handling
        $message = new Message();
        $messageForm = $this->createForm(MessageOrderFileRejectType::class, $message);
        $messageForm->handleRequest($request);
        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $message->setOrder($order);
            $message->setAuthor($this->getUser());
            $message->setAuthorMaker(false);
            $message->setText('Le livrable a été refusé. Raison du refus :' .chr(13) . $message->getText());

            $entityManager->persist($message);
            $entityManager->flush();
        }

        // update order status
        $orderManager->updateStatus($order, Order::STATUS_FILE_REJECTED, OrderEvent::ORIGIN_CUSTOMER);

        // redirect
        return new RedirectResponse($this->generateUrl('order_customer_see', array('reference' => $order->getReference())));
    }

    /**
     * Manage updates, done by the customer
     *
     * @Route("/customer/order/{reference}/status/{newStatus}/update", requirements={"newStatus" = "\d+"}, name="order_customer_status_update")
     *
     * @param Order $order
     * @param int $newStatus : as defined in Order class STATUS_* constants
     * @param OrderManager $orderManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerStatusUpdateAction(Order $order, $newStatus, OrderManager $orderManager)
    {
        // make sure the customer can edit this order
        if ($order->getCustomer() !== $this->getUser()) {
            throw new NotFoundHttpException();
        }


        // update the order status
        $orderManager->updateStatus($order, $newStatus, OrderEvent::ORIGIN_CUSTOMER);

        // redirect to the order page
        return $this->redirectToRoute('order_customer_see', array('reference' => $order->getReference()));
    }

    /**
     * Change orders status from downloaded to validated if download occurred more than 2 working days ago.
     * Meant to be called in a cron task.
     *
     * Note: would be better to execute that trough a Command but that causes issue with router configuration (which is
     * needed when sending transactional e-mail notification via OrderListener). For now calling it through an URL is
     * easier to set up (same as for shipments tracking).
     * @see https://symfony.com/doc/3.4/console/request_context.html
     *
     * @Route("/cron/order/update-downloaded-to-validated", name="order_cron_update_downloaded_to_validated")
     *
     * @param OrderManager $orderManager
     * @return Response
     */
    public function cronUpdateDownloadedToValidatedAction(OrderManager $orderManager)
    {
        $orderManager->updateDownloadedOrderToValidated(2);
        $orderManager->updateModelBuyedOrderToValidated(5);
        return new Response();
    }

    /**    
      * @Route("/batch/maker/update-stripe-representative-id", name="batch_update_representative_id")
     *
     * @param ObjectManager $entityManager
     * @param StripeManager $stripeManager
     * @return Response
     */

    public function updateRepresentativeId (Request $request, ObjectManager $entityManager, StripeManager $stripeManager)
    {

        // execution de l'URL avec le parametre ?MAJ=OK force la mise a jour dans la BDD
        $makers = [];
        $Log='<h1>BATCH MAJ REPRESENTATIVE ID' ;

        $MajBdd = $request->query->get('MAJ','KO');
        $Log=$Log . '<br> Mise a jour en base : ' . $MajBdd .'</h1><h3>';
        $makers  = $entityManager->getRepository('AppBundle:Maker')->findMakersWhereRepresentativeIsNull(); 
        foreach ($makers as $maker ) {
            $mFirstName=strtoupper($maker->getFirstname());
            $mLastName=strtoupper($maker->getLastname());

            $account = $stripeManager->updateAccountForStripeEvol( $maker);

            $listPerson = $stripeManager->getListPerson($maker->getStripeId());
            $pFirstName=strtoupper($listPerson->data[0]['first_name']);
            $pLastName=strtoupper($listPerson->data[0]['last_name']);
            
            if (($mFirstName == $pFirstName) && ($mLastName == $pLastName)) {
                $Log = $Log . sprintf('<br><br>Match Person OK  : %s - %s ',$mFirstName, $mLastName );
                if ($MajBdd == 'OK'){
                    $maker->setStripeRepresentativeId($listPerson->data[0]['id']);
                    $Log = $Log . ' ==> MAJ BDD done' ;
                }
            }else{
                $Log = $Log . sprintf('<br><br> NO MATCH for  : %s - %s ==> %s %s',$mFirstName, $mLastName,$pFirstName, $pLastName ) ;
                $Log = $Log . sprintf('<br> Maker ID :%s ==> Person ID (stripe) - %s',$maker->getid() , $listPerson->data[0]['id'] );
            }
           
        }
        $Log = $Log . '</h3>' ;
        $entityManager->flush();



        return new Response('<html><body>'.$Log.'</body></html>');
    }
}