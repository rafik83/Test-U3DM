<?php

namespace AppBundle\Event;

use AppBundle\Entity\Quotation;
use Symfony\Component\EventDispatcher\Event;

class QuotationEvent extends Event
{
    /**
     * Constants
     */
    const ORIGIN_SYSTEM   = 'system';
    const ORIGIN_ADMIN    = 'admin';
    const ORIGIN_MAKER    = 'maker';
    const ORIGIN_CUSTOMER = 'customer';

    /**
     * @var Quotation
     */
    private $quotation;

    /**
     * @var string
     */
    private $origin;

    /**
     * ProjectEvent constructor.
     *
     * @param Quotation $quotation
     * @param string $origin
     */
    public function __construct(Quotation $quotation, $origin = self::ORIGIN_SYSTEM)
    {
        $this->quotation = $quotation;
        $this->origin = $origin;
    }

    /**
     * @return Quotation
     */
    public function getQuotation()
    {
        return $this->quotation;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}