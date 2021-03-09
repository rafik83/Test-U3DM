<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModelStatus
 *
 * @ORM\Table(name="ref_model_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelStatusRepository")
 */
class ModelStatus
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
     * @ORM\Column(name="name", type="string", length=191, unique=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name_english", type="string", length=191, nullable=true)
     */
    private $nameEnglish;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=191, nullable=true)
     */
    private $description;


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
     * Set name.
     *
     * @param string $name
     *
     * @return ModelStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set nameEnglish.
     *
     * @param string|null $nameEnglish
     *
     * @return ModelStatus
     */
    public function setNameEnglish($nameEnglish = null)
    {
        $this->nameEnglish = $nameEnglish;

        return $this;
    }

    /**
     * Get nameEnglish.
     *
     * @return string|null
     */
    public function getNameEnglish()
    {
        return $this->nameEnglish;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return ModelStatus
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
