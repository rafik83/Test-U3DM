<?php

namespace AppBundle\Event;

use AppBundle\Entity\Signal;
use Symfony\Component\EventDispatcher\Event;

class SignalEvent extends Event
{
    /**
     * Constants
     */
    const ORIGIN_SYSTEM   = 'system';
    const ORIGIN_ADMIN    = 'admin';
    const ORIGIN_MAKER    = 'maker';
    const ORIGIN_CUSTOMER = 'customer';

    /**
     * @var Signal
     */
    private $signal;

    /**
     * @var string
     */
    private $origin;

    /**
     * ProjectEvent constructor.
     *
     * @param Signal $signal
     * @param string $origin
     */
    public function __construct(Signal $signal, $origin = self::ORIGIN_SYSTEM)
    {
        $this->signal = $signal;
        $this->origin = $origin;
    }

    /**
     * @return Signal
     */
    public function getSignal()
    {
        return $this->signal;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}