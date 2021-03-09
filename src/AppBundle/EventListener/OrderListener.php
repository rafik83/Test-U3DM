<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderStatusUpdate;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Project;
use AppBundle\Entity\Quotation;
use AppBundle\Entity\Refund;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Shipment;
use AppBundle\Entity\Shipping;
use AppBundle\Entity\FollowUpUser;
use AppBundle\Event\OrderEvent;
use AppBundle\Event\OrderEvents;
use AppBundle\Event\ProjectEvent;
use AppBundle\Event\QuotationEvent;
use AppBundle\Service\OrderManager;
use AppBundle\Service\ProjectManager;
use AppBundle\Service\QuotationManager;
use AppBundle\Service\SendinBlue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class OrderListener implements EventSubscriberInterface
{
    private $router;

    private $tokenGenerator;

    private $entityManager;

    private $orderManager;

    private $sendinBlue;

    private $quotationManager;

    private $projectManager;

    public function __construct(RouterInterface $router, ObjectManager $entityManager,TokenGeneratorInterface $tokenGenerator, OrderManager $orderManager, SendinBlue $sendinBlue, QuotationManager $quotationManager, ProjectManager $projectManager)
    {
        $this->router = $router;
        $this->tokenGenerator = $tokenGenerator;
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
        $this->sendinBlue = $sendinBlue;
        $this->quotationManager = $quotationManager;
        $this->projectManager = $projectManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            OrderEvents::PRE_PERSIST        => 'generateOrderReference',
            OrderEvents::PRE_STATUS_UPDATE  => 'addOrderStatusUpdate',
            OrderEvents::SET_TOKEN_ORDER  => 'setTokenOrder',
            OrderEvents::FOLLOW_UP_RATING_0  => 'FollowUp0',
            OrderEvents::FOLLOW_UP_RATING_1  => 'FollowUp1',
            OrderEvents::FOLLOW_UP_RATING_2  => 'FollowUp2',
            OrderEvents::FOLLOW_UP_RATING_3  => 'FollowUp3',
            OrderEvents::POST_STATUS_UPDATE => array(array('updateOrderReadyDate', 15), array('sendStatusUpdateNotifications', 10), array('payMaker', 0), array('handleCancellation', -10), array('manageQuotationProject', -11), array('handleOrderFileValidation', -12))
        );
    }

    /**
     * Generate an unique order reference
     *
     * @param OrderEvent $event
     */
    public function generateOrderReference(OrderEvent $event)
    {
        $order = $event->getOrder();
        $letters = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $numbers = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $reference = strtoupper($letters) . $numbers;
        $order->setReference($reference);
    }

    /**
     * Add an order status update to the order
     *
     * @param OrderEvent $event
     */
    public function addOrderStatusUpdate(OrderEvent $event)
    {
        $order = $event->getOrder();
        $statusUpdate = new OrderStatusUpdate();
        $statusUpdate->setStatus($order->getStatus());
        $statusUpdate->setOrigin($event->getOrigin());
        $order->addStatusUpdate($statusUpdate);
    }

    /**
     * Set the order "should be ready" date if order is new (meaning the order has been paid)
     *
     * @param OrderEvent $event
     */
    public function updateOrderReadyDate(OrderEvent $event)
    {
        $order = $event->getOrder();

        if (Order::STATUS_NEW === (int)$order->getStatus()) {

            // get default production time setting value
            $productionTime = $this->entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_PRODUCTION_TIME)->getValue();

            // if order is related to a quotation, get the production time from the quotation instead
            if (null !== $order->getQuotation()) {
                $productionTime = $order->getQuotation()->getProductionTime();
            }

            // RUN-IMP-4: -1 on production time
            if (1 <= $productionTime) {
                $productionTime--;
            }

            // set the date when the order should be ready, depending on production time and starting from now (meaning the order has been paid)
            $shouldBeReadyAt = $order->getShouldBeReadyDate($productionTime, new \DateTime('now', new \DateTimeZone('UTC')));
            $order->setShouldBeReadyAt($shouldBeReadyAt);

            // flush
            $this->entityManager->flush();
        }
    }



    /**
     * Set the order token
     *
     * @param OrderEvent $event
     */
    public function setTokenOrder (OrderEvent $event)
    {
        $order = $event->getOrder();
        $token = $this->tokenGenerator->generateToken();
        $order->setToken($token);

    }

    /**
     * Send e-mail notifications upon order status update
     *
     * @param OrderEvent $event
     */
    public function sendStatusUpdateNotifications(OrderEvent $event)
    {
        $order = $event->getOrder();
        switch ($order->getStatus()) {

            case Order::STATUS_AWAITING_SEPA:
                // send customer e-mail
                $customerEmailVars = array(
                    'customerName'    => $order->getCustomer()->getFullname(),
                    'orderReference'  => $order->getReference(),
                    'orderDate'       => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'shippingType'    => Shipping::getReadableShippingType($order->getShippingType()),
                    'accountUrl'      => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                    'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountTaxIncl() / 100), 2)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_AWAITING_SEPA,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $customerEmailVars
                );

                break;

            case Order::STATUS_NEW:
                // get payment
                $payment = $order->getPayments()->first();

                // is transport
                $isTransport = true;
                if (Shipping::TYPE_NOT_SHIPPED === $order->getShippingType()) {
                    $isTransport = false;
                }

                // send customer e-mail
                $customerEmailVars = array(
                    'customerName'    => $order->getCustomer()->getFullname(),
                    'invoiceUrl'      => $this->router->generate('order_invoice_download', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                    'orderReference'  => $order->getReference(),
                    'orderDate'       => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'paymentId'       => $payment->getChargeId(),
                    'shippingType'    => Shipping::getReadableShippingType($order->getShippingType()),
                    'BoolTransport'   => $isTransport,
                    'shippingAddress' => $order->getShippingAddress()->getFlatAddress(),
                    'accountUrl'      => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                    'expectedDeliveryDate'    => null !== $order->getExpectedDeliveryDate() ? $order->getExpectedDeliveryDate()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y') : '',
                    'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountTaxIncl() / 100), 2)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_CONFIRMATION_CUSTOMER,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $customerEmailVars
                );

                // send maker e-mail
                $makerEmailVars = array(
                    'makerName'       => $order->getMaker()->getFullname(),
                    'orderReference'  => $order->getReference(),
                    'orderDate'       => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'shippingType'    => Shipping::getReadableShippingType($order->getShippingType()),
                    'BoolTransport'   => $isTransport,
                    'accountUrl'      => $this->router->generate('order_maker_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                    'shippingLimitDate'       => null !== $order->getShouldBeReadyAt() ? $order->getShouldBeReadyAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y') . ' avant 17h' : '',
                    'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountForMakerTaxIncl() / 100), 2)// only display production amount - coupon amount to maker
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_CONFIRMATION_MAKER,
                    $order->getMaker()->getUser()->getEmail(),
                    $order->getMaker()->getFullname(),
                    $makerEmailVars
                );

                break;

            case Order::STATUS_CANCELED:
                // order has been canceled by the customer
                if (OrderEvent::ORIGIN_CUSTOMER === $event->getOrigin()) {

                    // get order payment id
                    $paymentId = 'n/a';
                    $payments = $order->getPayments();
                    if (0 < count($payments)) {
                        /** @var Payment $payment */
                        $payment = $payments[0];
                        $paymentId = $payment->getChargeId();
                    }

                    // send customer e-mail
                    $customerEmailVars = array(
                        'customerName'   => $order->getCustomer()->getFullname(),
                        'orderReference' => $order->getReference(),
                        'orderDate'      => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                        'paymentId'      => $paymentId,
                        'accountUrl'     => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                        'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountTaxIncl() / 100), 2)
                    );
                    $this->sendinBlue->sendTransactional(
                        SendinBlue::TEMPLATE_ID_ORDER_CANCELLATION_CUSTOMER,
                        $order->getCustomer()->getEmail(),
                        $order->getCustomer()->getFullname(),
                        $customerEmailVars
                    );

                    // send maker e-mail
                    $makerEmailVars = array(
                        'makerName'      => $order->getMaker()->getFullname(),
                        'orderReference' => $order->getReference(),
                        'orderDate'      => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                        'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountForMakerTaxIncl() / 100), 2)// only display production amount - coupon amount to maker
                    );
                    $this->sendinBlue->sendTransactional(
                        SendinBlue::TEMPLATE_ID_ORDER_CANCELLATION_MAKER,
                        $order->getMaker()->getUser()->getEmail(),
                        $order->getMaker()->getFullname(),
                        $makerEmailVars
                    );
                }
                break;

            case Order::STATUS_TRANSIT:

                // get shipment
                $shipments = $order->getShipments();
                /** @var Shipment $shipment */
                $shipment = $shipments[0];

                // send customer e-mail
                $emailVars = array(
                    'customerName'    => $order->getCustomer()->getFullname(),
                    'orderReference'  => $order->getReference(),
                    'shippingType'    => Shipping::getReadableShippingType($order->getShippingType()),
                    'shippingAddress' => $order->getShippingAddress()->getFlatAddress(),
                    'trackingUrl'     => $shipment->getTrackingUrl(),
                    'trackingNumber'  => $shipment->getParcelNumber(),
                    'accountUrl'      => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                    'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountTaxIncl() / 100), 2)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_SHIPPED,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $emailVars
                );

                break;

            case Order::STATUS_READY_FOR_PICKUP:

                // send customer e-mail
                $emailVars = array(
                    'customerName'    => $order->getCustomer()->getFullname(),
                    'orderReference'  => $order->getReference(),
                    'orderDate'       => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'shippingType'    => Shipping::getReadableShippingType($order->getShippingType()),
                    'shippingAddress' => $order->getShippingAddress()->getFlatAddress(),
                    'accountUrl'      => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL),
                    'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountTaxIncl() / 100), 2)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_READY_FOR_PICKUP,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $emailVars
                );

                break;

            case Order::STATUS_REFUNDED:

                // get order payment id
                $paymentId = 'n/a';
                $payments = $order->getPayments();
                if (0 < count($payments)) {
                    /** @var Payment $payment */
                    $payment = $payments[0];
                    $paymentId = $payment->getChargeId();
                }

                // get order refund id
                $refundId = 'n/a';
                $refunds = $order->getRefunds();
                if (0 < count($refunds)) {
                    /** @var Refund $refund */
                    $refund = $refunds[0];
                    $refundId = $refund->getRefundId();
                }

                // send customer e-mail
                $customerEmailVars = array(
                    'customerName'   => $order->getCustomer()->getFullname(),
                    'orderReference' => $order->getReference(),
                    'orderDate'      => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'paymentId'      => $paymentId,
                    'refundId'       => $refundId,
                    'orderTotalAmountTaxIncl' => number_format(($order->getTotalAmountTaxIncl() / 100), 2)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_REFUNDED,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $customerEmailVars
                );

                break;

            case Order::STATUS_DELIVERED:

                // send customer e-mail
                /* There is no longer any immediate notification sending when the order is delivered. The notification is made on D + 1 via the reminder system
                $customerEmailVars = array(
                    'customerName'   => $order->getCustomer()->getFullname(),
                    'orderReference' => $order->getReference(),
                    //'ratingUrl'      => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL) . '#rating'
                    'ratingUrl'      => $this->router->generate('order_rating', array('token' => $order->getToken()), $this->router::ABSOLUTE_URL) . '#rating'
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_RATING,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $customerEmailVars
                );
                */
                break;

                case Order::STATUS_CLOSED:

                    if ($order->getRating()->getEnabled() == True ) {
                        // send maker e-mail
                        $makerEmailVars = array(
                            'makerName'      => $order->getMaker()->getFullname(),
                            'orderReference' => $order->getReference(),
                            'orderDate'      => $order->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                            'orderRateValue' => $order->getRating()->getRate(),
                            'orderRateValue' => $order->getRating()->getComment(),
                            'accountUrl'     => $this->router->generate('order_maker_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL)

                        );
                        $this->sendinBlue->sendTransactional(
                            SendinBlue::TEMPLATE_ID_ORDER_RATE,
                            $order->getMaker()->getUser()->getEmail(),
                            $order->getMaker()->getFullname(),
                            $makerEmailVars
                        );
                    }
                    break;




            case Order::STATUS_FILE_AVAILABLE:

                // send customer e-mail
                $customerEmailVars = array(
                    'customerName'   => $order->getCustomer()->getFullname(),
                    'orderReference' => $order->getReference(),
                    'accountUrl'     => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_FILE_AVAILABLE_CUSTOMER,
                    $order->getCustomer()->getEmail(),
                    $order->getCustomer()->getFullname(),
                    $customerEmailVars
                );

                break;

            case Order::STATUS_FILE_REJECTED:

                // send maker e-mail
                $makerEmailVars = array(
                    'makerName'      => $order->getMaker()->getFullname(),
                    'orderReference' => $order->getReference(),
                    'accountUrl'     => $this->router->generate('order_maker_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_FILE_REJECTED_MAKER,
                    $order->getMaker()->getUser()->getEmail(),
                    $order->getMaker()->getFullname(),
                    $makerEmailVars
                );

                break;

            case Order::STATUS_FILE_VALIDATED:

                // send maker e-mail
                $makerEmailVars = array(
                    'makerName'      => $order->getMaker()->getFullname(),
                    'orderReference' => $order->getReference(),
                    'BoolTransport'  => $order->getQuotation()->getProject()->getType()->isShipping(),
                    'accountUrl'     => $this->router->generate('order_maker_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL)
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_ORDER_FILE_VALIDATED_MAKER,
                    $order->getMaker()->getUser()->getEmail(),
                    $order->getMaker()->getFullname(),
                    $makerEmailVars
                );

                break;
        }
    }

    /**
     * Send Notification to rate the order.
     *
     * @param OrderEvent $event
     */
    public function followUp0 (OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->followUp ( $order, SendinBlue::TEMPLATE_ID_ORDER_RATING, "TEMPLATE_ID_ORDER_RATING_F0");

    }
    /**
     * First followUp 
     *
     * @param OrderEvent $event
     */
    public function followUp1 (OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->followUp ( $order, SendinBlue::TEMPLATE_ID_ORDER_RATING_F1, "TEMPLATE_ID_ORDER_RATING_F1");

    }
    /**
     * Second followUp 
     *
     * @param OrderEvent $event
     */
    public function followUp2 (OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->followUp ( $order, SendinBlue::TEMPLATE_ID_ORDER_RATING_F2, "TEMPLATE_ID_ORDER_RATING_F2");

    }
    /**
     * Third followUp 
     *
     * @param OrderEvent $event
     */
    public function followUp3 (OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->followUp ( $order, SendinBlue::TEMPLATE_ID_ORDER_RATING_F3, "TEMPLATE_ID_ORDER_RATING_F3");

    }



    private function followUp (order $order, $sendinblueId, $sendinblueTemplateName)
    {
        // send customer e-mail
        $customerEmailVars = array(
            'customerName'   => $order->getCustomer()->getFullname(),
            'orderReference' => $order->getReference(),
            //'ratingUrl'      => $this->router->generate('order_customer_see', array('reference' => $order->getReference()), $this->router::ABSOLUTE_URL) . '#rating'
            'ratingUrl'      => $this->router->generate('order_rating', array('token' => $order->getToken()), $this->router::ABSOLUTE_URL) . '#rating'
        );
        $this->sendinBlue->sendTransactional(
            $sendinblueId,
            $order->getCustomer()->getEmail(),
            $order->getCustomer()->getFullname(),
            $customerEmailVars
        );

        // Store that un email was send
        $followUpUser = new FollowUpUser ();
        $followUpUser->setUser($order->getCustomer());
        $followUpUser->setTypeRef(FollowUpUser::TYPE_ORDER);
        $followUpUser->setRefId($order->getId());
        $followUpUser->setEvent($sendinblueTemplateName);


        $this->entityManager->persist($followUpUser);
        // flush
        $this->entityManager->flush();
    }



    /**
     * Pay the maker if necessary
     *
     * @param OrderEvent $event
     */
    public function payMaker(OrderEvent $event)
    {
        $order = $event->getOrder();

        // return here if maker has already been paid
        if ($order->isMakerPaid()) {
            return;
        }

        // pay if order has been shipped or is ready for pickup, or is delivered (useful for digital design orders)
        switch ($order->getStatus()) {
            case Order::STATUS_TRANSIT:
            case Order::STATUS_READY_FOR_PICKUP:
            case Order::STATUS_DELIVERED:
                $this->orderManager->payMaker($order);
                break;
            case Order::STATUS_MODEL_PAID:
                $this->orderManager->payMaker($order);
                break;
        }
    }

    /**
     * Handle order cancellation (refund the customer, ...)
     *
     * @param OrderEvent $event
     */
    public function handleCancellation(OrderEvent $event)
    {
        $order = $event->getOrder();

        if (Order::STATUS_CANCELED === $order->getStatus()) {

            // refund customer
            $this->orderManager->refund($order);

            // update coupon count if necessary
            if (null !== $order->getCoupon()) {
                $coupon = $order->getCoupon();
                if (null !== $coupon->getRemainingStock()) {
                    $coupon->setRemainingStock($coupon->getRemainingStock() + 1);
                    $this->entityManager->flush();
                }
            }
        }
    }

    /**
     * Manage Quotation & Project (if order status is STATUS_NEW & type = design)
     *
     * @param OrderEvent $event
     */
    public function manageQuotationProject(OrderEvent $event)
    {
        $order = $event->getOrder();

        if (Order::STATUS_NEW === $order->getStatus() && Order::TYPE_DESIGN === $order->getType()) {

            $project = $order->getQuotation()->getProject();

            // Project Update Status
            $this->projectManager->updateStatus($project, Project::STATUS_ORDERED, ProjectEvent::ORIGIN_SYSTEM);

            //Foreach Quotation update status : 1 STATUS_ACCEPTED / X STATUS_DISCARDED, only if current status is STATUS_DISPATCHED and STATUS_CLOSED for other
            $quotations = $project->getQuotations();
            foreach ($quotations as $quotation) {
                /** @var Quotation $quotation */
                if (Quotation::STATUS_REFUSED != $quotation->getStatus()) {
                    $status = Quotation::STATUS_CLOSED;
                    if ((Quotation::STATUS_DISPATCHED === $quotation->getStatus()) or (Quotation::STATUS_SENT === $quotation->getStatus()) or (Quotation::STATUS_NOT_DISPATCHED === $quotation->getStatus())  ) {
                        $status = Quotation::STATUS_DISCARDED;
                        if($quotation->getId() == $order->getQuotation()->getId()){
                            $status = Quotation::STATUS_ACCEPTED;
                        }
                    } 
                    $this->quotationManager->updateStatus($quotation, $status, QuotationEvent::ORIGIN_SYSTEM);
                }
            }

        }
    }

    /**
     * Handle design orders file validation workflow
     *
     * @param OrderEvent $event
     */
    public function handleOrderFileValidation(OrderEvent $event)
    {
        $order = $event->getOrder();
        if (Order::STATUS_FILE_VALIDATED === (int)$order->getStatus() && null !== $order->getQuotation() && false === $order->getQuotation()->getProject()->getType()->isShipping()&& false === $order->getQuotation()->getProject()->getType()->isShippingChoice()) {
            // change order status to delivered (will pay the maker)
            $this->orderManager->updateStatus($order, Order::STATUS_DELIVERED, OrderEvent::ORIGIN_SYSTEM);
        }
    }
}