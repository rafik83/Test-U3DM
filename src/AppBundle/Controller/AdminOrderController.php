<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\Order;
use AppBundle\Entity\Model;

use AppBundle\Event\OrderEvent;
use AppBundle\Service\OrderManager;
use AppBundle\Entity\Rating;
use AppBundle\Form\RatingType;

use AppBundle\Form\SignalType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/%app.admin_directory%/order")
 */
class AdminOrderController extends Controller
{
    /**
     * @Route("/list", name="admin_order_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listAction(ObjectManager $entityManager)
    {
        $orders = $entityManager->getRepository('AppBundle:Order')->findAllOrders();
        return $this->render('admin/order/list.html.twig', array('orders' => $orders));
    }

    /**
     * @Route("/{reference}", name="admin_order_see")
     *
     * @param Order $order
     * @return Response
     */
    public function seeAction(Order $order, ObjectManager $entityManager)
    {
        $models = null;
        
        if ($order->getType() === 'model') {
            $models = $entityManager->getRepository('AppBundle:Model')->findModelFromOrder($order);
        }
        return $this->render('admin/order/see.html.twig', array(
            'order' => $order,
            'models' => $models
        ));
    }

    /**
     * Generate a new label
     *
     * @Route("/{reference}/generateLabel", name="admin_order_generate_label")
     *
     * @param Order $order
     * @param OrderManager $orderManager
     * @return Response
     */
    public function generateLabelAction(Order $order, OrderManager $orderManager)
    {
        try {
            $orderManager->generateLabel($order, OrderEvent::ORIGIN_ADMIN);
            $this->addFlash('success', 'Une nouvelle étiquette a bien été générée.');
        } catch(\Exception $exception) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la création de l\'étiquette, veuillez ré-essayer ou contacter le support technique.');
        }
        return $this->redirectToRoute('admin_order_see', array('reference' => $order->getReference()));
    }

    /**
     * Manage updates, done by the admin
     *
     * @Route("/{reference}/status/{newStatus}/update", requirements={"newStatus" = "\d+"}, name="admin_order_status_update")
     *
     * @param Order $order
     * @param int $newStatus : as defined in Order class STATUS_* constants
     * @param OrderManager $orderManager
     * @return Response
     */
    public function statusUpdateAction(Order $order, $newStatus, OrderManager $orderManager)
    {
        // make sure the status update is legit
        $currentStatus = $order->getStatus();
        $allowedStatuses = array();
        switch ($currentStatus) {
            case Order::STATUS_AWAITING_SEPA:
                $allowedStatuses = array(Order::STATUS_NEW, Order::STATUS_REFUSED_SEPA);
                break;
            case Order::STATUS_PROCESSING:
                $allowedStatuses = array(Order::STATUS_CANCELED);
                break;
            case Order::STATUS_LABELED:
                $allowedStatuses = array(Order::STATUS_TRANSIT);
                break;
            case Order::STATUS_TRANSIT:
                $allowedStatuses = array(Order::STATUS_PND, Order::STATUS_DELIVERED);
                break;
            case Order::STATUS_MODEL_BUY:
                $allowedStatuses = array(Order::STATUS_CANCELED);
                break;
        }
        if (!in_array($newStatus, $allowedStatuses)) {
            throw new AccessDeniedException();
        }

        // update the order status
        $orderManager->updateStatus($order, (int)$newStatus, OrderEvent::ORIGIN_ADMIN);

        // redirect to the order page
        return $this->redirectToRoute('admin_order_see', array('reference' => $order->getReference()));
    }

    /**
     * @Route("/rating/list", name="admin_rating_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listRatingAction(ObjectManager $entityManager)
    {
        $ratings = $entityManager->getRepository('AppBundle:Rating')->findAllRatingByStatus(false);
        return $this->render('admin/rating/list.html.twig', array('ratings' => $ratings));
    }

    /**
     * @Route("/rating/{rating}/edit", requirements={"rating" = "\d+"}, name="admin_rating_edit")
     *
     * @param Rating $rating
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return Response
     */
    public function ratingEditAction(Rating $rating, Request $request, ObjectManager $entityManager, OrderManager $orderManager)
    {

        $form = $this->createForm(RatingType::class, $rating,['admin_user'=> true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            if ($rating->getEnabled()== TRUE) {
                $orderManager->updateStatus($rating->getOrder(), Order::STATUS_CLOSED, OrderEvent::ORIGIN_SYSTEM);
            }    
            $this->addFlash('success', 'admin.rating.edit.title');
            return $this->redirectToRoute('admin_rating_list');
        }

        return $this->render('admin/rating/form.html.twig', array('form' => $form->createView(), 'rating' => $rating));
    }

    /**
     * @Route("/rating/{rating}/enabled", name="admin_rating_enabled")
     *
     * @param ObjectManager $entityManager
     * @param Rating $rating
     * @param OrderManager $orderManager
     * @return Response
     */
    public function enabledRatingAction(Rating $rating, ObjectManager $entityManager, OrderManager $orderManager)
    {

        $rating->setEnabled(true);
       
        $entityManager->persist($rating);
        $entityManager->flush();
        $orderManager->updateStatus($rating->getOrder(), Order::STATUS_CLOSED, OrderEvent::ORIGIN_SYSTEM); 

        $this->addFlash('success', 'Avis client validé');

        return $this->redirectToRoute('admin_rating_list');
    }

    /**
     * @Route("/rating/{rating}/delete", name="admin_rating_delete")
     *
     * @param ObjectManager $entityManager
     * @param Rating $rating
     * @return Response
     */
    public function deleteRatingAction(Rating $rating, ObjectManager $entityManager)
    {

        $order = $rating->getOrder();

        $order->setRating(null);

        $entityManager->remove($rating);
        $entityManager->flush();

        $this->addFlash('success', 'Avis client supprimé');

        return $this->redirectToRoute('admin_rating_list');
    }

    /**
     * Manually pay the maker, if allowed
     *
     * @Route("/{reference}/manualMakerPayment", name="admin_order_manual_maker_payment")
     *
     * @param Order $order
     * @param OrderManager $orderManager
     * @return Response
     */
    public function manualMakerPaymentAction(Order $order, OrderManager $orderManager)
    {
        if ($order->isAllowedToManuallyPayTheMaker()) {
            $orderManager->payMaker($order);
        }

        return $this->redirectToRoute('admin_order_see', array('reference' => $order->getReference()));
    }

    /**
     * @Route("/message/{id}/attachment/download", name="admin_order_message_attachment_download")
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

    /**
     * @Route("/comment/list", name="admin_comment_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listCommentAction(ObjectManager $entityManager)
    {
        $comments = $entityManager->getRepository('AppBundle:ModelComments')->findAllCommentNotValid();
        return $this->render('admin/modelComment/list.html.twig', array('comments' => $comments));
    }

    

    
}