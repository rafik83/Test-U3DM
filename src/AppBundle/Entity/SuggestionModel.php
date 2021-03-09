<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SuggestionModel
 *
 * @ORM\Table(name="suggestion_model")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SuggestionModelRepository")
 * @Vich\Uploadable
 */
class SuggestionModel
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
     * @var int
     *
     * @ORM\Column(name="percentage", type="integer")
     * 
     */
    private $percentage;


    

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $categoryUp;

    /**
     * @var Suggestion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Suggestion", inversedBy="suggestionModel")
     * @ORM\JoinColumn(nullable=false)
     */
    private $suggestion;


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
     * Set percentage.
     *
     * @param int $percentage
     *
     * @return SuggestionModel
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


    /**
     * Set categoryUp
     *
     * @param Category $categoryUp
     *
     * @return SuggestionModel
     */
    public function setCategoryUp(Category $categoryUp)
    {
        $this->categoryUp = $categoryUp;

        return $this;
    }

    /**
     * Get categoryUp
     *
     * @return SuggestionModel
     */
    public function getCategoryUp()
    {
        return $this->categoryUp;
    }

    /**
     * Set suggestion
     *
     * @param Suggestion $suggestion
     *
     * @return SuggestionModel
     */
    public function setSuggestion(Suggestion $suggestion)
    {
        $this->suggestion = $suggestion;

        return $this;
    }

    /**
     * Get suggestion
     *
     * @return SuggestionModel
     */
    public function getSuggestion()
    {
        return $this->suggestion;
    }

}
