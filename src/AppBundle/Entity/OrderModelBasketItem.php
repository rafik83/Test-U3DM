<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderModelBasketItem
 *
 * @ORM\Table(name="order_model_basket_item")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderModelBasketItemRepository")
 */
class OrderModelBasketItem
{
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="orderModelBasket")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var OrderModelBasket
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OrderModelBasket", inversedBy="orderModelBasketList")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderModelBasket;

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
     * @return OrderModelBasketItem
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set orderModelBasket
     *
     * @param OrderModelBasket $orderModelBasket
     *
     * @return OrderModelBasketItem
     */
    public function setOrderModelBasket(OrderModelBasket $orderModelBasket)
    {
        $this->orderModelBasket = $orderModelBasket;

        return $this;
    }

    /**
     * Get orderModelBasket
     *
     * @return OrderModelBasket
     */
    public function getOrderModelBasket()
    {
        return $this->orderModelBasket;
    }
}
