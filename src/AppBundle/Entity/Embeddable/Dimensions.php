<?php

namespace AppBundle\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dimensions
 *
 * @ORM\Embeddable()
 */
class Dimensions
{
    /**
     * @var float
     *
     * @ORM\Column(name="x", type="float", nullable=true)
     */
    private $x;

    /**
     * @var float
     *
     * @ORM\Column(name="y", type="float", nullable=true)
     */
    private $y;

    /**
     * @var float
     *
     * @ORM\Column(name="z", type="float", nullable=true)
     */
    private $z;


    /**
     * Dimensions constructor
     *
     * @param float|null $x
     * @param float|null $y
     * @param float|null $z
     */
    public function __construct($x = null, $y = null, $z = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Set x
     *
     * @param float|null $x
     *
     * @return Dimensions
     */
    public function setX($x = null)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return float|null
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param float|null $y
     *
     * @return Dimensions
     */
    public function setY($y = null)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return float|null
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set z
     *
     * @param float|null $z
     *
     * @return Dimensions
     */
    public function setZ($z = null)
    {
        $this->z = $z;

        return $this;
    }

    /**
     * Get z
     *
     * @return float|null
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * Check if self dimensions fits into the provided max dimensions
     *
     * @param Dimensions $maxDimensions
     * @return bool
     */
    public function fitsInto(Dimensions $maxDimensions)
    {
        if (!$this->isValid() || !$maxDimensions->isValid()) {
            return false;
        }

        $maxDimensionsValues = array($maxDimensions->getX(), $maxDimensions->getY(), $maxDimensions->getZ());
        sort($maxDimensionsValues);

        $dimensionsValues = array($this->getX(), $this->getY(), $this->getZ());
        sort($dimensionsValues);

        return (
            $dimensionsValues[0] <= $maxDimensionsValues[0] &&
            $dimensionsValues[1] <= $maxDimensionsValues[1] &&
            $dimensionsValues[2] <= $maxDimensionsValues[2]
        );
    }

    /**
     * Check if self dimensions fits into the provided min dimensions
     *
     * @param Dimensions $minDimensions
     * @return bool
     */
    public function fitsIntoMin(Dimensions $minDimensions)
    {
        if (!$this->isValid() || !$minDimensions->isValid()) {
            return false;
        }

        $minDimensionsValues = array($minDimensions->getX(), $minDimensions->getY(), $minDimensions->getZ());
        sort($minDimensionsValues);

        $dimensionsValues = array($this->getX(), $this->getY(), $this->getZ());
        sort($dimensionsValues);

        return (
            $dimensionsValues[0] >= $minDimensionsValues[0] &&
            $dimensionsValues[1] >= $minDimensionsValues[1] &&
            $dimensionsValues[2] >= $minDimensionsValues[2]
        );
    }

    /**
     * Check that all three dimensions values are not null
     *
     * @return bool
     */
    public function isValid()
    {
        return (null !== $this->getX() && null !== $this->getY() && null !== $this->getZ());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $started = false;
        $res = '';
        if (null !== $this->x) {
            $res .= $this->x;
            $started = true;
        }
        if (null !== $this->y) {
            if ($started) {
                $res .= ' x ';
            }
            $res .= $this->y;
            $started = true;
        }
        if (null !== $this->z) {
            if ($started) {
                $res .= ' x ';
            }
            $res .= $this->z;
        }
        return $res;
    }
}