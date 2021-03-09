<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Rating
 *
 * @ORM\Table(name="rating")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RatingRepository")
 */
class Rating
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

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
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var int
     *
     * @ORM\Column(name="rate", type="integer")
     */
    private $rate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="ratings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Maker", inversedBy="ratings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $maker;

    /**
     * @var Order
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Order", mappedBy="rating")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;


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
     * Set comment
     *
     * @param string|null $comment
     *
     * @return Rating
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set rate
     *
     * @param int $rate
     *
     * @return Rating
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return Rating
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set customer
     *
     * @param User $customer
     *
     * @return Rating
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return User
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set maker
     *
     * @param Maker $maker
     *
     * @return Rating
     */
    public function setMaker(Maker $maker)
    {
        $this->maker = $maker;

        return $this;
    }

    /**
     * Get maker
     *
     * @return Maker
     */
    public function getMaker()
    {
        return $this->maker;
    }

    /**
     * Set order
     *
     * @param Order $order
     *
     * @return Rating
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set rateToOrder
     *
     * @param Order $order
     *
     * @return Rating
     */
    public function setRateToOrder(Order $order, $rate,$comment = null)
    {
        $this->order = $order;
        $this->customer = $order->getcustomer();
        $this->maker = $order->getMaker();
        $this->rate = $rate;
        $this->comment = $comment;
        $this->enabled = true;
        return $this;
    }


}
