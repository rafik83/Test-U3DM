<?php

namespace AppBundle\Event;

use AppBundle\Entity\Order;
use Symfony\Component\EventDispatcher\Event;

class OrderEvent extends Event
{
    /**
     * Constants
     */
    const ORIGIN_SYSTEM   = 'system';
    const ORIGIN_ADMIN    = 'admin';
    const ORIGIN_MAKER    = 'maker';
    const ORIGIN_CUSTOMER = 'customer';

    /**
     * @var Order
     */
    private $order;

    /**
     * @var string
     */
    private $origin;

    /**
     * OrderEvent constructor.
     *
     * @param Order $order
     * @param string $origin
     */
    public function __construct(Order $order, $origin = self::ORIGIN_SYSTEM)
    {
        $this->order = $order;
        $this->origin = $origin;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}