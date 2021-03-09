<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * FollowUpUser
 *
 * @ORM\Table(name="follow_up_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FollowUpUserRepository")
 */
class FollowUpUser
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Types
     */
    const TYPE_ORDER  = 'Order';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="FollowUpUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="type_ref", type="string", length=255)
     */
    private $typeRef;

    /**
     * @var int
     *
     * @ORM\Column(name="ref_id", type="integer")
     */
    private $refId;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=255, nullable=false)
     * 
     */
    private $event;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user.
     *
     * @param int $user
     *
     * @return FollowUpUser
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set typeRef.
     *
     * @param string $typeRef
     *
     * @return FollowUpUser
     */
    public function setTypeRef($typeRef)
    {
        $this->typeRef = $typeRef;

        return $this;
    }

    /**
     * Get typeRef.
     *
     * @return string
     */
    public function getTypeRef()
    {
        return $this->typeRef;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return FollowUpUser
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set event.
     *
     * @param int $event
     *
     * @return FollowUpUser
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return int
     */
    public function getEvent()
    {
        return $this->event;
    }
}
