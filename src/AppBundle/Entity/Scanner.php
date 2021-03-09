<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Embeddable\Dimensions;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Scanner
 *
 * @ORM\Table(name="scanner")
 * @ORM\Entity()
 */
class Scanner
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

    /**
     * @var string
     *
     * @ORM\Column(name="brand", type="string", length=255)
     */
    private $brand;

    /**
     * @var TechnologyScanner
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TechnologyScanner")
     * @ORM\JoinColumn(nullable=false)
     */
    private $technology;

    /**
     * @var Precision
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Precision")
     * @ORM\JoinColumn(nullable=false)
     */
    private $precision;

    /**
     * @var Resolution
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Resolution")
     * @ORM\JoinColumn(nullable=false)
     */
    private $resolution;

    /**
     * @var Dimensions : in mm
     *
     * @ORM\Embedded(class="AppBundle\Entity\Embeddable\Dimensions", columnPrefix="dimensions_min_")
     */
    private $minDimensions;

    /**
     * @var Dimensions : in mm
     *
     * @ORM\Embedded(class="AppBundle\Entity\Embeddable\Dimensions", columnPrefix="dimensions_max_")
     */
    private $maxDimensions;

    /**
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Maker", inversedBy="scanners")
     * @ORM\JoinColumn(nullable=false)
     */
    private $maker;


    /**
     * Printer constructor
     */
    public function __construct()
    {
        $this->visible = false;
        $this->minDimensions = new Dimensions();
        $this->maxDimensions = new Dimensions();
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
     * @return Scanner
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Scanner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set visible
     *
     * @param bool $visible
     *
     * @return Scanner
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Set brand
     *
     * @param string $brand
     *
     * @return Scanner
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set technology
     *
     * @param TechnologyScanner $technology
     *
     * @return Scanner
     */
    public function setTechnology(TechnologyScanner $technology)
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return TechnologyScanner
     */
    public function getTechnology()
    {
        return $this->technology;
    }

    /**
     * Set precision
     *
     * @param Precision $precision
     *
     * @return Scanner
     */
    public function setPrecision(Precision $precision)
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * Get precision
     *
     * @return Precision
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Set resolution
     *
     * @param Resolution $resolution
     *
     * @return Scanner
     */
    public function setResolution(Resolution $resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get resolution
     *
     * @return Resolution
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * Set min dimensions
     *
     * @param Dimensions $dimensions
     *
     * @return Scanner
     */
    public function setMinDimensions($dimensions)
    {
        $this->minDimensions = $dimensions;

        return $this;
    }

    /**
     * Get min dimensions
     *
     * @return Dimensions
     */
    public function getMinDimensions()
    {
        return $this->minDimensions;
    }

    /**
     * Set max dimensions
     *
     * @param Dimensions $dimensions
     *
     * @return Scanner
     */
    public function setMaxDimensions($dimensions)
    {
        $this->maxDimensions = $dimensions;

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
     * Set maker
     *
     * @param Maker $maker
     *
     * @return Scanner
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
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }
}
