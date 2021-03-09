<?php
 
namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\Rating;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Refund;

use AppBundle\Entity\Shipment;
use AppBundle\Entity\Shipping;

use AppBundle\Event\OrderEvent;
use AppBundle\Event\OrderEvents;
use AppBundle\Service\SendinBlue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Psr\Log\LoggerInterface;

class OrderManager
{
    private $entityManager;

    private $eventDispatcher;

    private $stripeManager;

    private $colissimo;

    private $chronopost;

    private $logger;


    /**
     * OrderManager constructor
     *
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param StripeManager $stripeManager
     * @param Colissimo $colissimo
     * @param Chronopost $chronopost
     */
    public function __construct(ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher, StripeManager $stripeManager, Colissimo $colissimo, Chronopost $chronopost, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->stripeManager = $stripeManager;
        $this->colissimo = $colissimo;
        $this->chronopost = $chronopost;

        $this->logger = $logger;
    }

    /**
     * Update the order status
     *
     * @param Order $order
     * @param int $newStatus
     * @param string $origin
     */
    public function updateStatus(Order $order, $newStatus, $origin)
    {

       
        // update order status
        $order->setStatus($newStatus);

        // dispatch an event
        $this->eventDispatcher->dispatch(OrderEvents::PRE_STATUS_UPDATE, new OrderEvent($order, $origin));

        if ($newStatus == Order::STATUS_DELIVERED ){
            $this->eventDispatcher->dispatch(OrderEvents::SET_TOKEN_ORDER, new OrderEvent($order));
        }

        // flush
        $this->entityManager->flush();

        // dispatch an event
        $this->eventDispatcher->dispatch(OrderEvents::POST_STATUS_UPDATE, new OrderEvent($order, $origin));

    }

    /**
     * Generate a shipment label
     *
     * @param Order $order
     * @param string $origin
     */
    public function generateLabel(Order $order, $origin = OrderEvent::ORIGIN_MAKER)
    {


        // remove the previous shipment, if any (as we are considering only one shipment per order for now)
        foreach ($order->getShipments() as $shipment) {
            $order->removeShipment($shipment);
        }

        // use the right service depending on shipping type (we may generate a label for a not-shipped order to be used for object return to customer)
        if ($order->getShippingType() === Shipping::TYPE_HOME_STANDARD || ($order->getType() === Order::TYPE_DESIGN && $order->getShippingType() === Shipping::TYPE_NOT_SHIPPED)) {
            
            // call Colissimo WS
            $colissimoResponse = $this->colissimo->generateLabel($order->getShippingAddress());

            // create a shipment
            $shipment = new Shipment();
            $shipment->setType(Shipment::TYPE_COLISSIMO);
            $shipment->setParcelNumber($this->colissimo->getGenerateLabelParcelNumber($colissimoResponse));
            $shipment->setLabelPdfUrl($this->colissimo->getGenerateLabelPdfUrl($colissimoResponse));

            // add shipment to the order
            $order->addShipment($shipment);

            // update order status
            $this->updateStatus($order, Order::STATUS_LABELED, $origin);

        } elseif ($order->getShippingType() === Shipping::TYPE_HOME_EXPRESS || $order->getShippingType() === Shipping::TYPE_RELAY) {

            // handle relay
            $relay = false;
            $relayIdentifier = null;
            if ($order->getShippingType() === Shipping::TYPE_RELAY) {
                $relay = true;
                $relayIdentifier = $order->getShippingRelayIdentifier();
            }

            // call Chronopost WS
            $chronopostData = $this->chronopost->generateLabel($order->getShippingAddress(), $relay, $relayIdentifier);


            // create a shipment
            $shipment = new Shipment();
            $shipment->setType(Shipment::TYPE_CHRONOPOST);
            $shipment->setParcelNumber($chronopostData['parcelNumber']);
            $shipment->setLabelPdfUrl($chronopostData['pdfUrl']);


            // add shipment to the order
            $order->addShipment($shipment);


            // update order status
            $this->updateStatus($order, Order::STATUS_LABELED, $origin);
        }
    }


    /**
     * Pay the maker
     *
     * @param Order $order
     */
    public function payMaker(Order $order)
    {
        // stop here if maker was already paid
        if ($order->isMakerPaid()) {
            return;
        }

        // get maker
        $maker = $order->getMaker();

        // proceed to maker payment
        if (null !== $maker->getStripeId()) {

            // get maker cut
            $makerPaymentAmount = $order->getMakerCutAmountTaxIncl();

            try {
                // transfer due amount to the maker stripe account
                $makerTransfer = $this->stripeManager->createTransfer($makerPaymentAmount, 'eur', $maker->getStripeId(), $order->getReference());

                if ($makerTransfer instanceof \Stripe\Transfer) {

                    // transfer succeeded, flag the order as paid to the maker and flush
                    $order->setMakerPaid(true);
                    $this->entityManager->flush();
                }

            } catch (\Exception $e) {
                // deal with transfer error...
            }
        }
    }

    /**
     * Refund the order
     *
     * @param Order $order
     */
    public function refund(Order $order)
    {
        // get order payment id
        $paymentId = null;
        $payments = $order->getPayments();
        if (0 < count($payments)) {
            /** @var Payment $payment */
            $payment = $payments[0];
            $paymentId = $payment->getChargeId();
            $paymenttype = $payment->getType();
        }

        if ((null !== $paymentId) &&  ($paymenttype != Payment::TYPE_VIREMENT)) {

            // proceed to refund
            $stripeRefund = $this->stripeManager->createRefundCharge($paymentId, $order->getTotalAmountTaxIncl());

            if ($stripeRefund instanceof \Stripe\Refund) {

                // create the refund
                $refund = new Refund();
                $refund->setAmount($order->getTotalAmountTaxIncl());
                $refund->setRefundId($stripeRefund->id);

                // add the refund to the order
                $order->addRefund($refund);

                // update status to refunded
                $this->updateStatus($order, Order::STATUS_REFUNDED, OrderEvent::ORIGIN_SYSTEM);
            }
        } else if ($paymenttype == Payment::TYPE_VIREMENT) {

            // create the refund
            $refund = new Refund();
            $refund->setAmount($order->getTotalAmountTaxIncl());
            $refund->setRefundId("re_u3dm_virement");

            // add the refund to the order
            $order->addRefund($refund);

            // update status to refunded
            $this->updateStatus($order, Order::STATUS_REFUNDED, OrderEvent::ORIGIN_SYSTEM);
            

        }
    }

    /**
     * Track the order shipment and update status accordingly
     *
     * @param Order $order
     * @return void
     */
    public function trackShipmentAndUpdateOrder(Order $order)
    {
        // get the order shipment (assume we have only one for now)
        $parcelNumber = null;
        foreach ($order->getShipments() as $shipment) {
            /** @var Shipment $shipment */
            $parcelNumber = $shipment->getParcelNumber();
            break;
        }

        // quit here if there is no shipment
        if (null === $parcelNumber) {
            return;
        }

        // call the right tracking WS method depending on shipment type
        if (Shipping::TYPE_HOME_STANDARD === $order->getShippingType()) {
            // Colissimo tracking
            $trackingEventCode = $this->colissimo->track($parcelNumber);
            if (false !== $trackingEventCode) {
                // event code is described by the INOVERT codification
                switch ($trackingEventCode) {
                    case 'PCHCFM':
                    case 'PCHTAR':
                        // order is handled by carrier
                        if (Order::STATUS_LABELED === $order->getStatus()) {
                            $this->updateStatus($order, Order::STATUS_TRANSIT, OrderEvent::ORIGIN_SYSTEM);
                        }
                        break;
                    case 'RENDIA':
                    case 'RENDID':
                    case 'RENDIV':
                    case 'RENSRB':
                    case 'RENTAR':
                        // PND
                        if (Order::STATUS_TRANSIT === $order->getStatus()) {
                            $this->updateStatus($order, Order::STATUS_PND, OrderEvent::ORIGIN_SYSTEM);
                        }
                        break;
                    case 'LIVCFM':
                    case 'LIVGAR':
                    case 'LIVRTI':
                    case 'LIVVOI':
                        // order has been delivered
                        if (Order::STATUS_TRANSIT === $order->getStatus()) {
                            $this->updateStatus($order, Order::STATUS_DELIVERED, OrderEvent::ORIGIN_SYSTEM);
                        }
                        break;
                }
            }

        } elseif (Shipping::TYPE_HOME_EXPRESS === $order->getShippingType() || Shipping::TYPE_RELAY === $order->getShippingType()) {
            // Chronopost tracking
            // get order tracking event codes (Chronopost provides all events, not just the last one like Colissimo does)
            $trackingEventCodes = $this->chronopost->track($parcelNumber);

            if (Order::STATUS_LABELED === $order->getStatus()) {
                // check if order has been in transit
                $transitCodes = array('A', 'A1', 'A2', 'AB', 'AS', 'AT', 'BA', 'DB', 'DV', 'DY', 'EC', 'ED', 'LR', 'MD', 'PC', 'PE', 'RB', 'SC', 'SD', 'TA', 'TI', 'TO', 'TP', 'TS');
                foreach ($transitCodes as $code) {
                    if (in_array($code, $trackingEventCodes)) {
                        $this->updateStatus($order, Order::STATUS_TRANSIT, OrderEvent::ORIGIN_SYSTEM);
                        break;
                    }
                }
            }

            if (Order::STATUS_TRANSIT === $order->getStatus()) {
                // check if order has been delivered
                $deliveredCodes = array('B', 'D', 'DD', 'RG', 'RI', 'VC');
                foreach ($deliveredCodes as $code) {
                    if (in_array($code, $trackingEventCodes)) {
                        $this->updateStatus($order, Order::STATUS_DELIVERED, OrderEvent::ORIGIN_SYSTEM);
                        break;
                    }
                }
            }

            if (Order::STATUS_TRANSIT === $order->getStatus()) {
                // check if order has been PND
                $pndCodes = array('CA', 'PR', 'R');
                foreach ($pndCodes as $code) {
                    if (in_array($code, $trackingEventCodes)) {
                        $this->updateStatus($order, Order::STATUS_PND, OrderEvent::ORIGIN_SYSTEM);
                        break;
                    }
                }
            }
        }

        return;
    }


    /**
     * Look for orders with statuts DELEVERY if a notatification need to be send.
     *
     * @param Order $order
     * @return void
     */
    public function  CustomerFollowUpRating()
    {
        
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $now->add(new \DateInterval('P0D'));
        $orders = $this->entityManager->getRepository('AppBundle:Order')->findOrdersToRate();
        foreach ($orders as $order) {
            // dispatch an event
            if ($order->getToken() == null) {
                $this->eventDispatcher->dispatch(OrderEvents::SET_TOKEN_ORDER, new OrderEvent($order));
            }
            $DeliveredAt = $order->getDeliveredAt ();
            $nbDay = $DeliveredAt->diff( $now)->format('%a');


            $this->logger->info($order->getCustomer()->getLastname() .' .('.$order->getCustomer()->getId().') pas de notation depuis '.$nbDay.' jours. REFID' . $order->getId());

            if ($nbDay >= 28 ){
                $this->logger->info('Relance' . $order->getCustomer()->getLastname() .' pas de notation depuis 28J');
                $rate = new rating() ;
                $rate->setRateToOrder($order,5);
                $order->setRating($rate);
                $this->entityManager->persist($rate);
                $this->updateStatus($order, Order::STATUS_CLOSED, OrderEvent::ORIGIN_SYSTEM);
 
                
            }elseif ($nbDay >= 21) {
                $result = $this->entityManager->getRepository('AppBundle:FollowUpUser')->findOrderEventLog($order,"TEMPLATE_ID_ORDER_RATING_F3");
                if ($result == null) {
                    $this->logger->info('Relance' . $order->getCustomer()->getLastname() .' pas de notation depuis 21J');
                    $this->eventDispatcher->dispatch(OrderEvents::FOLLOW_UP_RATING_3, new OrderEvent($order));
                }
            }elseif ($nbDay >= 14) {
                $result = $this->entityManager->getRepository('AppBundle:FollowUpUser')->findOrderEventLog($order,"TEMPLATE_ID_ORDER_RATING_F2");
                if ($result == null) {
                    $this->logger->info('Relance' . $order->getCustomer()->getLastname() .' pas de notation depuis 14J');
                    $this->eventDispatcher->dispatch(OrderEvents::FOLLOW_UP_RATING_2, new OrderEvent($order));
                }
                    
            }elseif ($nbDay >= 7) {
                $result = $this->entityManager->getRepository('AppBundle:FollowUpUser')->findOrderEventLog($order,"TEMPLATE_ID_ORDER_RATING_F1");
                if ($result == null) {
                    $this->logger->info('Relance' . $order->getCustomer()->getLastname() .' pas de notation depuis 7J');
                    $this->eventDispatcher->dispatch(OrderEvents::FOLLOW_UP_RATING_1, new OrderEvent($order));
                }
            }elseif ($nbDay >= 1) {
                $result = $this->entityManager->getRepository('AppBundle:FollowUpUser')->findOrderEventLog($order,"TEMPLATE_ID_ORDER_RATING_F0");
                if ($result == null) {
                    $this->logger->info('Relance' . $order->getCustomer()->getLastname() .' pas de notation depuis 7J');
                    $this->eventDispatcher->dispatch(OrderEvents::FOLLOW_UP_RATING_0, new OrderEvent($order));
                }               
            }

        }
        $this->entityManager->flush();

    }



    /**
     * Look for orders with STATUS_FILE_DOWNLOADED status and update it to STATUS_FILE_VALIDATED if download occurred
     * more than <numberOfWorkingDays> working days ago.
     *
     * @param int $numberOfWorkingDays
     * @return string[] array of updated orders references
     */
    public function updateDownloadedOrderToValidated($numberOfWorkingDays = 2)
    {
        $result = array();

        // get all orders with STATUS_FILE_DOWNLOADED status
        $orders = $this->entityManager->getRepository('AppBundle:Order')->findByStatus(Order::STATUS_FILE_DOWNLOADED);

        foreach($orders as $order) {
            /** @var Order $order */
            $fileDownloadedAt = $order->getFileDownloadedAt();
            if (null !== $fileDownloadedAt) {
                // add the requested number of working days to the download date
                $updateStatusAt = $order->addWorkingDays($fileDownloadedAt, $numberOfWorkingDays);
                // update the order status to STATUS_FILE_VALIDATED if needed
                $now = new \DateTime('now', new \DateTimeZone('UTC'));
                if ($now > $updateStatusAt) {
                    $this->updateStatus($order, Order::STATUS_FILE_VALIDATED, OrderEvent::ORIGIN_SYSTEM);
                    $result[] = $order->getReference();
                }
            }
        }

        return $result;
    }
    
    /**
     * Look for orders with STATUS_FILE_DOWNLOADED status and update it to STATUS_FILE_VALIDATED if download occurred
     * more than <numberOfWorkingDays> working days ago.
     *
     * @param int $numberOfWorkingDays
     * @return string[] array of updated orders references
     */
    public function updateModelBuyedOrderToValidated($numberOfWaitingDays)
    {
        $result = array();

        // get all orders with STATUS_FILE_DOWNLOADED status
        $orders = $this->entityManager->getRepository('AppBundle:Order')->findByStatus(Order::STATUS_MODEL_BUY);

        foreach($orders as $order) {
            /** @var Order $order */
            $modelByAt = $order->getCreatedAt();
            if (null !== $modelByAt) {
                // add the requested number of working days to the download date
                $updateStatusAt = $order->addWorkingDays($modelByAt, $numberOfWaitingDays);
                // update the order status to STATUS_FILE_VALIDATED if needed
                $now = new \DateTime('now', new \DateTimeZone('UTC'));
                if ($now > $updateStatusAt) {
                    $this->updateStatus($order, Order::STATUS_MODEL_PAID, OrderEvent::ORIGIN_SYSTEM);
                    $this->updateStatus($order, Order::STATUS_CLOSED, OrderEvent::ORIGIN_SYSTEM);
                    $result[] = $order->getReference();
                }
            }
        }

        return $result;
    }
}