<?php

namespace AppBundle\Event;

use AppBundle\Entity\Payment;
use Symfony\Component\EventDispatcher\Event;

class PaymentEvent extends Event
{
    /**
     * @var Payment
     */
    private $payment;

    /**
     * PaymentEvent constructor.
     *
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}