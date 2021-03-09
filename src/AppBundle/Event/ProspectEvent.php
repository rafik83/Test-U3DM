<?php

namespace AppBundle\Event;

use AppBundle\Entity\Prospect;
use Symfony\Component\EventDispatcher\Event;

class ProspectEvent extends Event
{
    /**
     * @var Prospect
     */
    private $prospect;


    /**
     * ProspectEvent constructor
     *
     * @param Prospect $prospect
     */
    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * @return Prospect
     */
    public function getProspect()
    {
        return $this->prospect;
    }
}