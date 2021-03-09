<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Printer
 *
 * @ORM\Table(name="printer_product_finishing")
 * @ORM\Entity()
 */
class PrinterProductFinishing
{
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
     * @var PrinterProduct
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrinterProduct", inversedBy="finishings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @var Finishing
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Finishing")
     * @ORM\JoinColumn(nullable=false)
     */
    private $finishing;

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
     * Set id
     *
     * @param int $id
     *
     * @return PrinterProductFinishing
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set printer product
     *
     * @param PrinterProduct $product
     *
     * @return PrinterProductFinishing
     */
    public function setProduct(PrinterProduct $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get printer product
     *
     * @return PrinterProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set finishing
     *
     * @param Finishing $finishing
     *
     * @return PrinterProductFinishing
     */
    public function setFinishing(Finishing $finishing)
    {
        $this->finishing = $finishing;

        return $this;
    }

    /**
     * Get finishing
     *
     * @return Finishing
     */
    public function getFinishing()
    {
        return $this->finishing;
    }

    /**
     * Set price
     *
     * @param int $price
     *
     * @return PrinterProductFinishing
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }
}
