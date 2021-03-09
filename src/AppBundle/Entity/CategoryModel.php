<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryModel
 *
 * @ORM\Table(name="category_model")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryModelRepository")
 */
class CategoryModel
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
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="categoryModel")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;


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
     * Set model
     *
     * @param Model $model
     *
     * @return CategoryModel
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return CategoryModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return CategoryModel
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return CategoryModel
     */
    public function getCategory()
    {
        return $this->category;
    }
}
