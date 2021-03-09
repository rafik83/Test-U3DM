<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderModelBasket
 *
 * @ORM\Table(name="order_model_basket")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderModelBasketRepository")
 */
class OrderModelBasket
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="ordersBaskets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=100, unique=true)
     */
    private $reference;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderModelBasketItem", mappedBy="orderModelBasket", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $orderModelBasketList;

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
     * Set customer
     *
     * @param User $customer
     *
     * @return Order
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
     * Set reference
     *
     * @param string $reference
     *
     * @return Quotation
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Get orderModelBasketList item
     *
     * @return ArrayCollection
     */
    public function getOrderModelBasketItem()
    {
        return $this->orderModelBasketList;
    }

    /**
     * Add orderModelBasketList item
     *
     * @param OrderModelBasketItem $item
     */
    public function addOrderModelBasketItem(OrderModelBasketItem $item)
    {
        $item->setModel($this);

        $this->orderModelBasketList[] = $item;
    }

    /**
     * Remove orderModelBasketList item
     *
     * @param OrderModelBasketItem $item
     */
    public function removeOrderModelBasketItem(OrderModelBasketItem $item)
    {
        $this->orderModelBasketList->removeElement($item);
    }
}
