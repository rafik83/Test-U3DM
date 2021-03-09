<?php

namespace AppBundle\Event;

use AppBundle\Entity\ModelComments;
use Symfony\Component\EventDispatcher\Event;

class ModelCommentsEvent extends Event
{
    /**
     * Constants
     */
    const ORIGIN_SYSTEM   = 'system';
    const ORIGIN_ADMIN    = 'admin';
    const ORIGIN_MAKER    = 'maker';
    const ORIGIN_CUSTOMER = 'customer';

    /**
     * @var ModelComments
     */
    private $modelComments;

    /**
     * @var string
     */
    private $origin;

    /**
     * ProjectEvent constructor.
     *
     * @param ModelComments $modelComments
     * @param string $origin
     */
    public function __construct(ModelComments $modelComments, $origin = self::ORIGIN_SYSTEM)
    {
        $this->modelComments = $modelComments;
        $this->origin = $origin;
    }

    /**
     * @return ModelComments
     */
    public function getModelComments()
    {
        return $this->modelComments;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}