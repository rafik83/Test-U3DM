<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * EventLog
 */
class EventLog
{

/**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Types
     */
    const TYPE_ORDER_EVENT  = 'OrderEvents';

  /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type_event", type="string", length=20)
     */
    private $typeEvent;

    /**
     * @var int
     *
     * @ORM\Column(name="entity_event_id", type="int")
     */
    private $entityEventId;

    /**
     * @var int
     *
     * @ORM\Column(name="event_id", type="int")
     */
    private $eventId;
   


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


      /**
     * Set typeEvent
     *
     * @param string $typeEvent
     *
     * @return EventLog
     */
    public function setTypeEvent($typeEvent)
    {
        $this->type = $typeEvent;

        return $this;
    }

    /**
     * Get typeEvent
     *
     * @return string
     */
    public function getTypeEvent()
    {
        return $this->typeEvent;
    }


    /**
     * Set entityEventId
     *
     * @param int $entityEventId
     *
     * @return EventLog
     */
    public function setEntityEventId($entityEventId)
    {
        $this->entityEventId = $entityEventId;

        return $this;
    }

    /**
     * Get entityEventId
     *
     * @return int
     */
    public function getEntityEventId()
    {
        return $this->entityEventId;
    }


    /**
     * Set eventId
     *
     * @param int $eventId
     *
     * @return EventLog
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

}
