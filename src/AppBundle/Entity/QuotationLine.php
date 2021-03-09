<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuotationLine
 *
 * @ORM\Table(name="quotation_line")
 * @ORM\Entity()
 */
class QuotationLine
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
     * @var Quotation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quotation", inversedBy="lines")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quotation;

    /**
     * @var int
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @var int : price as cents
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;


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
     * Set quotation
     *
     * @param Quotation $quotation
     *
     * @return QuotationLine
     */
    public function setQuotation(Quotation $quotation)
    {
        $this->quotation = $quotation;

        return $this;
    }

    /**
     * Get quotation
     *
     * @return Quotation
     */
    public function getQuotation()
    {
        return $this->quotation;
    }

    /**
     * Set number
     *
     * @param int $number
     *
     * @return QuotationLine
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return QuotationLine
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     *
     * @return QuotationLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set price, in cents
     *
     * @param int $price
     *
     * @return QuotationLine
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price, in cents
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }
}
