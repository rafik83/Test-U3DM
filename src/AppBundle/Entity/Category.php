<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")
     * @ORM\JoinColumn(nullable=true)
     */
    private $upCategory;


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
     * @return Category
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
     * Set upName.
     *
     * @param string $upName
     *
     * @return Category
     */
    public function setUpName($upName)
    {
        $this->upName = $upName;

        return $this;
    }

    /**
     * Set upCategory
     *
     * @param Category $upCategory
     *
     * @return Category
     */
    public function setUpCategory(Category $upCategory)
    {
        $this->upCategory = $upCategory;

        return $this;
    }

    /**
     * Get upCategory
     *
     * @return Category
     */
    public function getUpCategory()
    {
        return $this->upCategory;
    }
    

    public function __toString() {
        return $this->name;
    }
}
