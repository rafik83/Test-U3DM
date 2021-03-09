<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ref_finishing")
 * @ORM\Entity()
 */
class Finishing
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
     * @var string
     *
     * @ORM\Column(name="name_short", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_long", type="string", length=255, nullable=true)
     */
    private $nameLong;

    /**
     * @var string
     *
     * @ORM\Column(name="name_english", type="string", length=255, nullable=true)
     */
    private $nameEnglish;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    // TODO $image

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $editorialLink;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;


    /**
     * Finishing constructor
     */
    public function __construct()
    {
        $this->enabled = true;
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
     * Set name
     *
     * @param string $name
     *
     * @return Finishing
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
     * Set long name
     *
     * @param string|null $name
     *
     * @return Finishing
     */
    public function setNameLong($name = null)
    {
        $this->nameLong = $name;

        return $this;
    }

    /**
     * Get long name
     *
     * @return string|null
     */
    public function getNameLong()
    {
        return $this->nameLong;
    }

    /**
     * Set english name
     *
     * @param string|null $name
     *
     * @return Finishing
     */
    public function setNameEnglish($name)
    {
        $this->nameEnglish = $name;

        return $this;
    }

    /**
     * Get english name
     *
     * @return string|null
     */
    public function getNameEnglish()
    {
        return $this->nameEnglish;
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return Finishing
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
     * Set editorial link
     *
     * @param string|null $link
     *
     * @return Finishing
     */
    public function setEditorialLink($link = null)
    {
        $this->editorialLink = $link;

        return $this;
    }

    /**
     * Get editorial link
     *
     * @return string|null
     */
    public function getEditorialLink()
    {
        return $this->editorialLink;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return Finishing
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
