<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Suggestion
 *
 * @ORM\Table(name="suggestion")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SuggestionRepository")
 * @Vich\Uploadable
 */
class Suggestion
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SuggestionModel", mappedBy="suggestion", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $suggestionModel;

    /**
     * @var int
     *
     */
    private $percentage;

    /**
     * Model constructor
     */
    public function __construct()
    {
        $this->suggestionModel = new ArrayCollection();
    }

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
     * @return Suggestion
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return Suggestion
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get product suggestionModel
     *
     * @return ArrayCollection
     */
    public function getSuggestionModel()
    {
        return $this->suggestionModel;
    }

    /**
     * Add a product suggestionModel
     *
     * @param SuggestionModel $suggestionModel
     *
     * @return Suggestion
     */
    public function addSuggestionModel(SuggestionModel $suggestionOfModel)
    {
        $suggestionOfModel->setSuggestion($this);

        $this->suggestionModel[] = $suggestionOfModel;

        return $this;
    }

    /**
     * Remove a product suggestionModel
     *
     * @param SuggestionModel $suggestionModel
     */
    public function removeSuggestionModel(SuggestionModel $suggestionOfModel)
    {
        $this->suggestionModel->removeElement($suggestionOfModel);
    }

    /**
     * Set percentage.
     *
     * @param int $percentage
     *
     * @return Suggestion
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage.
     *
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

}
