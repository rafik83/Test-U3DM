<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_item")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"print" = "OrderItemPrint", "print_finishing" = "OrderItemPrintFinishing", "design" = "OrderItemDesign"})
 */
abstract class OrderItem
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
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\Column(name="unit_amount_tax_incl", type="integer")
     */
    private $unitAmountTaxIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="unit_amount_tax_excl", type="integer")
     */
    private $unitAmountTaxExcl;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_rate", type="float")
     */
    private $taxRate;


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
     * Set order
     *
     * @param Order $order
     *
     * @return OrderItem
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
     * Set unit amount tax inclusive, in cents
     *
     * @param int $amount
     *
     * @return OrderItem
     */
    public function setUnitAmountTaxIncl($amount)
    {
        $this->unitAmountTaxIncl = $amount;

        return $this;
    }

    /**
     * Get unit amount tax inclusive, in cents
     *
     * @return int
     */
    public function getUnitAmountTaxIncl()
    {
        return $this->unitAmountTaxIncl;
    }

    /**
     * Set unit amount tax exclusive, in cents
     *
     * @param int $amount
     *
     * @return OrderItem
     */
    public function setUnitAmountTaxExcl($amount)
    {
        $this->unitAmountTaxExcl = $amount;

        return $this;
    }

    /**
     * Get unit amount tax exclusive, in cents
     *
     * @return int
     */
    public function getUnitAmountTaxExcl()
    {
        return $this->unitAmountTaxExcl;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     *
     * @return OrderItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set tax rate, in percent
     *
     * @param float $rate
     *
     * @return OrderItem
     */
    public function setTaxRate($rate)
    {
        $this->taxRate = $rate;

        return $this;
    }

    /**
     * Get tax rate, in percent
     *
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Abstract method: get the item description (to be displayed to the end-user)
     *
     * @return string
     */
    abstract public function getDescription();
}
