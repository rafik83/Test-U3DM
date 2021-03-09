<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * ModerationRule
 *
 * @ORM\Table(name="moderation_rules")
 * @ORM\Entity()
 */
class ModerationRule
{
    /**
     * Add createdAt and updatedAt fields
     */
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
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="expression", type="text",nullable=false)
     */
    private $expression;

    /**
     * @var string
     *
     * @ORM\Column(name="text_replace", type="text", nullable=true)
     */
    private $replace;
   
    /**
     * @var bool
     *
     * @ORM\Column(name="need_moderate", type="boolean",  options={"default" : 1})
     */
    private $needModerate;

    /**
     * @var Administrator
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Administrator")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @var Administrator
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Administrator")
     */
    private $lastModifiedBy;


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
     * Set expression.
     *
     * @param string $expression
     *
     * @return ModerationRule
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Get expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set replace.
     *
     * @param string $replace
     *
     * @return ModerationRule
     */
    public function setReplace($replace)
    {
        $this->replace = $replace;

        return $this;
    }

    /**
     * Get replace.
     *
     * @return string
     */
    public function getReplace()
    {
        return $this->replace;
    }

    /**
     * Set position.
     *
     * @param string $position
     *
     * @return ModerationRule
     */
    public function setPosition($position)
    {
        // If Priority is null Set = maxPriority +1 
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }



  /**
     * Set needModerate
     *
     * @param bool $needModerate
     *
     * @return ModerationRule

     */
    public function setNeedModerate($needModerate)
    {
        $this->needModerate = $needModerate;

        return $this;
    }

    /**
     * Get needModerate
     *
     * @return bool
     */
    public function getNeedModerate()
    {
        return $this->needModerate;
    }

    /**
     * is needModerate
     *
     * @return bool
     */
    public function isNeedModerate()
    {
        return $this->needModerate;
    }

/**
     * Set created by
     *
     * @param Administrator $createdBy
     *
     * @return ModerationRule
     */
    public function setCreatedBy(Administrator $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get created by
     *
     * @return Administrator
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set last modified by
     *
     * @param Administrator|null $lastModifiedBy
     *
     * @return ModerationRule
     */
    public function setLastModifiedBy(Administrator $lastModifiedBy = null)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get last modified by
     *
     * @return Administrator|null
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

}