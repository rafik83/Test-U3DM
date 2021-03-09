<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModelBuy
 *
 * @ORM\Table(name="model_buy")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelBuyRepository")
 */
class ModelBuy
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
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="modelBuy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="modelBuy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="modelBuy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var OrderModelUp
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OrderModelUp", inversedBy="modelBuy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderModelUp;


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
     * Set model
     *
     * @param Model $model
     *
     * @return ModelBuy
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return ModelBuy
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set customer
     *
     * @param User $customer
     *
     * @return ModelBuy
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return ModelBuy
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set orderModelUp
     *
     * @param OrderModelUp $orderModelUp
     *
     * @return ModelBuy
     */
    public function setOrderModelUp(OrderModelUp $orderModelUp)
    {
        $this->orderModelUp = $orderModelUp;

        return $this;
    }

    /**
     * Get orderModelUp
     *
     * @return ModelBuy
     */
    public function getOrderModelUp()
    {
        return $this->orderModelUp;
    }

    /**
     * Set order
     *
     * @param Order $order
     *
     * @return ModelBuy
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return ModelBuy
     */
    public function getOrder()
    {
        return $this->order;
    }
    
}
