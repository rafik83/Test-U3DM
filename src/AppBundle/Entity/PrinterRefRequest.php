<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * This is not a persisted entity, it is useful to map the RefRequestType form
 */
class PrinterRefRequest
{
    /**
     * Types
     */
    const TYPE_TECHNOLOGY = 'technology';
    const TYPE_MATERIAL   = 'material';
    const TYPE_COLOR      = 'color';
    const TYPE_FINISHING  = 'finishing';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $fillingRate;

    /**
     * @var ArrayCollection
     */
    private $technologies;

    /**
     * @var string
     */
    private $comments;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->technologies = new ArrayCollection();
    }

    /**
     * Set type
     *
     * @param string|null $type
     *
     * @return RefRequest
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     *
     * @param string|null $name
     *
     * @return RefRequest
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return RefRequest
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set filling rate
     *
     * @param bool $fillingRate
     *
     * @return RefRequest
     */
    public function setFillingRate($fillingRate = false)
    {
        $this->fillingRate = $fillingRate;

        return $this;
    }

    /**
     * Has filling rate
     *
     * @return bool
     */
    public function hasFillingRate()
    {
        return $this->fillingRate;
    }

    /**
     * Get technologies
     *
     * @return ArrayCollection
     */
    public function getTechnologies()
    {
        return $this->technologies;
    }

    /**
     * Add technology
     *
     * @param Technology $technology
     */
    public function addTechnology(Technology $technology)
    {
        $this->technologies[] = $technology;
    }

    /**
     * Remove technology
     *
     * @param Technology $technology
     */
    public function removeTechnology(Technology $technology)
    {
        $this->technologies->removeElement($technology);
    }

    /**
     * Set comments
     *
     * @param string|null $comments
     *
     * @return RefRequest
     */
    public function setComments($comments = null)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string|null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return string
     */
    public function getReadableType()
    {
        switch ($this->getType()) {
            case self::TYPE_TECHNOLOGY:
                $result = 'Technologie';
                break;
            case self::TYPE_MATERIAL:
                $result = 'Matériau';
                break;
            case self::TYPE_COLOR:
                $result = 'Couleur';
                break;
            case self::TYPE_FINISHING:
                $result = 'Finition';
                break;
            default:
                $result = 'Inconnu';
                break;
        }
        return $result;
    }
}