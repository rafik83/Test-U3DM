<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Embeddable\Dimensions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_item_print")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderItemPrintRepository")
 */
class OrderItemPrint extends OrderItem
{
    /**
     * @var PrintFile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrintFile")
     * @ORM\JoinColumn(nullable=false)
     */
    private $file;

    /**
     * @var Dimensions : in mm
     *
     * @ORM\Embedded(class="AppBundle\Entity\Embeddable\Dimensions", columnPrefix="dimensions_")
     */
    private $dimensions;

    /**
     * @var float : in mm3
     *
     * @ORM\Column(name="volume", type="float")
     */
    private $volume;

    /**
     * @var Technology
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Technology")
     * @ORM\JoinColumn(nullable=false)
     */
    private $technology;

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
     * @var Color
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Color")
     * @ORM\JoinColumn(nullable=false)
     */
    private $color;

    /**
     * @var int
     *
     * @ORM\Column(name="filling_rate", type="integer")
     */
    private $fillingRate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderItemPrintFinishing", mappedBy="printItem", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $itemFinishings;


    /**
     * OrderItemPrint constructor
     */
    public function __construct()
    {
        $this->dimensions = new Dimensions();
        $this->itemFinishings = new ArrayCollection();
    }

    /**
     * Set print file
     *
     * @param PrintFile $file
     * @return OrderItemPrint
     */
    public function setPrintFile(PrintFile $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get print file
     *
     * @return PrintFile
     */
    public function getPrintFile()
    {
        return $this->file;
    }

    /**
     * Set dimensions
     *
     * @param Dimensions $dimensions
     *
     * @return OrderItemPrint
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Get dimensions
     *
     * @return Dimensions
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Set volume
     *
     * @param float $volume
     *
     * @return OrderItemPrint
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return float
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set technology
     *
     * @param Technology $technology
     * @return OrderItemPrint
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
     * Set material
     *
     * @param Material $material
     * @return OrderItemPrint
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
     * @return OrderItemPrint
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
     * Set color
     *
     * @param Color $color
     * @return OrderItemPrint
     */
    public function setColor(Color $color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set filling rate
     *
     * @param int $rate
     * @return OrderItemPrint
     */
    public function setFillingRate($rate)
    {
        $this->fillingRate = $rate;

        return $this;
    }

    /**
     * Get filling rate
     *
     * @return int
     */
    public function getFillingRate()
    {
        return $this->fillingRate;
    }

    /**
     * Get readable filling rate
     *
     * @return string|null
     */
    public function getReadableFillingRate()
    {
        $result = null;
        $rate = $this->getFillingRate();
        switch ($rate) {
            case FillingRate::RATE_0_25:
                $result = '0-25 %';
                break;
            case FillingRate::RATE_26_50:
                $result = '26-50 %';
                break;
            case FillingRate::RATE_51_100:
                $result = '51-100 %';
                break;
            case FillingRate::NONE:
                $result = null;
                break;
            default:
                $result = $rate . ' %';
                break;
        }
        return $result;
    }

    /**
     * Get item finishings
     *
     * @return ArrayCollection
     */
    public function getItemFinishings()
    {
        return $this->itemFinishings;
    }

    /**
     * Add an item finishing
     *
     * @param OrderItemPrintFinishing $itemFinishing
     *
     * @return OrderItemPrint
     */
    public function addItemFinishing(OrderItemPrintFinishing $itemFinishing)
    {
        $itemFinishing->setPrintItem($this);

        $this->itemFinishings[] = $itemFinishing;

        return $this;
    }

    /**
     * Remove an item finishing
     *
     * @param OrderItemPrintFinishing $itemFinishing
     */
    public function removeItemFinishing(OrderItemPrintFinishing $itemFinishing)
    {
        $this->itemFinishings->removeElement($itemFinishing);
    }

    /**
     * Implement abstract method
     *
     * @return string
     */
    public function getDescription()
    {
        $printFileName = null !== $this->getPrintFile()->getOriginalName() ? $this->getPrintFile()->getOriginalName() : $this->getPrintFile()->getName();
        $result  = 'Objet 3D : ' . $printFileName;
        $result .= ' | ';
        $result .= $this->getDescriptionConfiguration();
        return $result;
    }

    /**
     * Implement abstract method
     *
     * @return string
     */
    public function getDescriptionConfiguration()
    {
        $result  = 'Dimensions : ' . $this->getDimensions()->getX() . ' x ' . $this->getDimensions()->getY() . ' x ' . $this->getDimensions()->getZ() . ' mm';
        $result .= ' | Volume : ' . ceil($this->getVolume() / 1000) . ' cm3';
        $result .= ' | Technologie : ' . $this->getTechnology()->getName();
        $result .= ' | Matériau : ' . $this->getMaterial()->getName();
        $result .= ' | Couleur : ' . $this->getColor()->getName();
        $result .= ' | Hauteur de couche : ' . $this->getLayer()->getHeightWithUnit();
        if (null !== $this->getReadableFillingRate()) {
            $result .= ' | Remplissage : ' . $this->getReadableFillingRate();
        }
        return $result;
    }

    /**
     * Set file.
     *
     * @param \AppBundle\Entity\PrintFile $file
     *
     * @return OrderItemPrint
     */
    public function setFile(\AppBundle\Entity\PrintFile $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return \AppBundle\Entity\PrintFile
     */
    public function getFile()
    {
        return $this->file;
    }
}
