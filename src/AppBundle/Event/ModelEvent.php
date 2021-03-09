<?php

namespace AppBundle\Event;

use AppBundle\Entity\Model;
use Symfony\Component\EventDispatcher\Event;

class ModelEvent extends Event
{
    /**
     * Constants
     */
    const ORIGIN_SYSTEM   = 'system';
    const ORIGIN_ADMIN    = 'admin';
    const ORIGIN_MAKER    = 'maker';
    const ORIGIN_CUSTOMER = 'customer';

    /**
     * @var Model
     */
    private $model;

    /**
     * @var string
     */
    private $origin;

    /**
     * ProjectEvent constructor.
     *
     * @param Model $model
     * @param string $origin
     */
    public function __construct(Model $model, $origin = self::ORIGIN_SYSTEM)
    {
        $this->model = $model;
        $this->origin = $origin;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}