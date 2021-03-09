<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Order;
use AppBundle\Entity\Payment;
use AppBundle\Event\PaymentEvent;
use AppBundle\Event\PaymentEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            PaymentEvents::PRE_PERSIST  => 'updateOrderStatus'
        );
    }

    /**
     * Update the order status if payment succeeded and order was awaiting payment
     * Also deal with SEPA payment
     *
     * @param PaymentEvent $event
     */
    public function updateOrderStatus(PaymentEvent $event)
    {
        $payment = $event->getPayment();
        $order = $payment->getOrder();
        if (Payment::CHARGE_STATUS_SUCCEEDED === $payment->getChargeStatus()) {
            if (Order::STATUS_AWAITING_PAYMENT === $order->getStatus()) {
                $order->setStatus(Order::STATUS_NEW);
            }
        } elseif (Payment::TYPE_SEPA === $payment->getType()) {
            if (Order::STATUS_AWAITING_PAYMENT === $order->getStatus()) {
                $order->setStatus(Order::STATUS_AWAITING_SEPA);
            }
        } elseif (Payment::TYPE_VIREMENT === $payment->getType()) {
            if (Order::STATUS_AWAITING_PAYMENT === $order->getStatus()) {
                $order->setStatus(Order::STATUS_AWAITING_SEPA);
            }
        }
    }
}