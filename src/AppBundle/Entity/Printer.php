<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Embeddable\Dimensions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Printer
 *
 * @ORM\Table(name="printer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PrinterRepository")
 */
class Printer
{
    use TimestampableEntity;

    /**
     * Constants
     */
    const STATUS_NOT_AVAILABLE       = 0; // printer is not available or there is no available printer product
    const STATUS_AVAILABLE           = 1; // printer and all its products are available
    const STATUS_PARTIALLY_AVAILABLE = 2; // printer is available but at least one of its products is not available


    const VOLUME_METHODE_MATERIAL       = 0; // Use the material volume to calculate the price
    const VOLUME_METHODE_BOUNDING_BOX   = 1; // Use the object's dimension (bounding box) to calculate the price
  
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
     * @ORM\Column(name="model", type="string", length=255)
     */
    private $model;
   
    /**
     * @var Dimensions : in mm
     *
     * @ORM\Embedded(class="AppBundle\Entity\Embeddable\Dimensions", columnPrefix="dimensions_max_")
     */
    private $maxDimensions;

    /**
     * @var float : in mm3
     *
     * @ORM\Column(name="volume_min", type="float")
     */
    private $minVolume;

    /**
     * @var int : price as cents
     *
     * @ORM\Column(name="price_setup", type="integer")
     */
    private $setupPrice;

     /**
     * @var int : Methode to calculate volume
     *
     * @ORM\Column(name="methode_volume", type="integer", options={"default" : 0})
     */
    private $volumeMethode = 0;
  

    /**
     * @var bool
     *
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;

    /**
     * @var Technology
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Technology")
     * @ORM\JoinColumn(nullable=false)
     */
    private $technology;

    /**
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Maker", inversedBy="printers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $maker;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PrinterProduct", mappedBy="printer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $products;


    /**
     * Printer constructor
     */
    public function __construct()
    {
        $this->maxDimensions = new Dimensions();
        $this->available = false;
        $this->products = new ArrayCollection();
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
     * @return Printer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set model
     *
     * @param string $model
     *
     * @return Printer
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set setup price
     *
     * @param int $setupPrice
     *
     * @return Printer
     */
    public function setSetupPrice($setupPrice)
    {
        $this->setupPrice = $setupPrice;

        return $this;
    }

    /**
     * Get setup price
     *
     * @return int
     */
    public function getSetupPrice()
    {
        return $this->setupPrice;
    }

  /**
     * Set Methode Value
     *
     * @param int $volumeMethode
     *
     * @return Printer
     */
    public function setVolumeMethode($volumeMethode)
    {
        $this->volumeMethode = $volumeMethode;
  
        return $this;
    }
   /**
    * Get Methode Value
    *
    * @return int
    */
    public function getVolumeMethode()
    {
        return $this->volumeMethode;
    }
  
 
 

    /**
     * Set max dimensions
     *
     * @param Dimensions $maxDimensions
     *
     * @return Printer
     */
    public function setMaxDimensions($maxDimensions)
    {
        $this->maxDimensions = $maxDimensions;

        return $this;
    }

    /**
     * Get max dimensions
     *
     * @return Dimensions
     */
    public function getMaxDimensions()
    {
        return $this->maxDimensions;
    }

    /**
     * Set min volume
     *
     * @param float $minVolume
     *
     * @return Printer
     */
    public function setMinVolume($minVolume)
    {
        $this->minVolume = $minVolume;

        return $this;
    }

    /**
     * Get min volume
     *
     * @return float
     */
    public function getMinVolume()
    {
        return $this->minVolume;
    }

    /**
     * Set available
     *
     * @param boolean $available
     *
     * @return Printer
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
     * Set technology
     *
     * @param Technology $technology
     *
     * @return Printer
     */
    public function setTechnology(Technology $technology)
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return Technology
     */
    public function getTechnology()
    {
        return $this->technology;
    }

    /**
     * Set maker
     *
     * @param Maker $maker
     *
     * @return Printer
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
     * Get products, ordered by:
     * - material name (asc)
     * - layer height (desc)
     * - price_100 (asc)
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        $iterator = $this->products->getIterator();
        $iterator->uasort(function($first, $second) {
            /** @var PrinterProduct $first */
            /** @var PrinterProduct $second */
            $firstMaterialName = $first->getMaterial()->getName();
            $secondMaterialName = $second->getMaterial()->getName();
            if ($firstMaterialName === $secondMaterialName) {
                $firstLayerHeight = $first->getLayer()->getHeight();
                $secondLayerHeight = $second->getLayer()->getHeight();
                if ($firstLayerHeight === $secondLayerHeight) {
                    $firstPrice = $first->getPrice100();
                    $secondPrice = $second->getPrice100();
                    if ($firstPrice === $secondPrice) {
                        return 0;
                    }
                    return $firstPrice < $secondPrice ? -1 : 1;
                }
                return $firstLayerHeight > $secondLayerHeight ? -1 : 1;
            }
            return strcasecmp($firstMaterialName, $secondMaterialName) < 0 ? -1 : 1;
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * Add a product
     *
     * @param PrinterProduct $product
     *
     * @return Printer
     */
    public function addProduct(PrinterProduct $product)
    {
        $product->setPrinter($this);

        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove a product
     *
     * @param PrinterProduct $product
     */
    public function removeProduct(PrinterProduct $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * @return int
     */
    public function getNumberOfAvailableProducts()
    {
        $result = 0;
        foreach ($this->getProducts() as $product) {
            /** @var PrinterProduct $product */
            if ($product->isAvailable()) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getNumberOfNotAvailableProducts()
    {
        return $this->getProducts()->count() - $this->getNumberOfAvailableProducts();
    }

    /**
     * Get the printer status, which must be of the STATUS_* class constants
     *
     * @return int
     */
    public function getStatus()
    {
        $result = self::STATUS_NOT_AVAILABLE;
        if ($this->isAvailable()) {
            $numberOfProducts = $this->getProducts()->count();
            if (0 < $numberOfProducts && 0 < $this->getNumberOfAvailableProducts()) {
                $result = self::STATUS_AVAILABLE;
                if ($numberOfProducts !== $this->getNumberOfAvailableProducts()) {
                    $result = self::STATUS_PARTIALLY_AVAILABLE;
                }
            }
        }
        return $result;
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
