<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Printer
 *
 * @ORM\Table(name="printer_product")
 * @ORM\Entity()
 */
class PrinterProduct
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
     * @var int : price as cents for a cm3 with a filling rate between 0% and 25%
     *
     * @ORM\Column(name="price_25", type="integer", nullable=true)
     */
    private $price25;

    /**
     * @var int : price as cents for a cm3 with a filling rate between 26% and 50%
     *
     * @ORM\Column(name="price_50", type="integer", nullable=true))
     */
    private $price50;

    /**
     * @var int : price as cents for a cm3 with a filling rate between 51% and 100%
     *
     * This is considered as the only price if the printer technology does not support several filling rates
     *
     * @ORM\Column(name="price_100", type="integer")
     */
    private $price100;

    /**
     * @var bool
     *
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;

    /**
     * @var Printer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Printer", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $printer;

    /**
     * @var Material
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Material")
     * @ORM\JoinColumn(nullable=false)
     */
    private $material;

    /**
     * @var Layer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Layer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $layer;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Color")
     * @ORM\JoinTable(name="printer_product_color")
     */
    private $colors;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PrinterProductFinishing", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $finishings;


    /**
     * PrinterProduct constructor
     */
    public function __construct()
    {
        $this->available = false;
        $this->colors = new ArrayCollection();
        $this->finishings = new ArrayCollection();
    }

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
     * @return PrinterProduct
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set 0-25 price
     *
     * @param int|null $price
     *
     * @return PrinterProduct
     */
    public function setPrice25($price = null)
    {
        $this->price25 = $price;

        return $this;
    }

    /**
     * Get 0-25 price
     *
     * @return int|null
     */
    public function getPrice25()
    {
        return $this->price25;
    }

    /**
     * Set 26-50 price
     *
     * @param int|null $price
     *
     * @return PrinterProduct
     */
    public function setPrice50($price = null)
    {
        $this->price50 = $price;

        return $this;
    }

    /**
     * Get 26-50 price
     *
     * @return int|null
     */
    public function getPrice50()
    {
        return $this->price50;
    }

    /**
     * Set 51-100 price
     *
     * @param int $price
     *
     * @return PrinterProduct
     */
    public function setPrice100($price)
    {
        $this->price100 = $price;

        return $this;
    }

    /**
     * Get 51-100 price
     *
     * @return int
     */
    public function getPrice100()
    {
        return $this->price100;
    }

    /**
     * Set available
     *
     * @param boolean $available
     *
     * @return PrinterProduct
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * Set printer
     *
     * @param Printer $printer
     *
     * @return PrinterProduct
     */
    public function setPrinter(Printer $printer)
    {
        $this->printer = $printer;

        return $this;
    }

    /**
     * Get printer
     *
     * @return Printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Set material
     *
     * @param Material $material
     *
     * @return PrinterProduct
     */
    public function setMaterial(Material $material)
    {
        $this->material = $material;

        return $this;
    }

    /**
     * Get material
     *
     * @return Material
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set layer
     *
     * @param Layer $layer
     *
     * @return PrinterProduct
     */
    public function setLayer(Layer $layer)
    {
        $this->layer = $layer;

        return $this;
    }

    /**
     * Get layer
     *
     * @return Layer
     */
    public function getLayer()
    {
        return $this->layer;
    }

    /**
     * Get colors
     *
     * @return ArrayCollection
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Add a color
     *
     * @param Color $color
     *
     * @return PrinterProduct
     */
    public function addColor(Color $color)
    {
        $this->colors[] = $color;

        return $this;
    }

    /**
     * Remove a color
     *
     * @param Color $color
     */
    public function removeColor(Color $color)
    {
        $this->colors->removeElement($color);
    }

    /**
     * Get product finishings
     *
     * @return ArrayCollection
     */
    public function getFinishings()
    {
        return $this->finishings;
    }

    /**
     * Add a product finishing
     *
     * @param PrinterProductFinishing $productFinishing
     *
     * @return PrinterProduct
     */
    public function addFinishing(PrinterProductFinishing $productFinishing)
    {
        $productFinishing->setProduct($this);

        $this->finishings[] = $productFinishing;

        return $this;
    }

    /**
     * Remove a product finishing
     *
     * @param PrinterProductFinishing $productFinishing
     */
    public function removeFinishing(PrinterProductFinishing $productFinishing)
    {
        $this->finishings->removeElement($productFinishing);
    }

    /**
     * Get available.
     *
     * @return bool
     */
    public function getAvailable()
    {
        return $this->available;
    }
}
