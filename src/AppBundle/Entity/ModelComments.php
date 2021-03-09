<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ModelComments
 *
 * @ORM\Table(name="model_comments")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelCommentsRepository")
 */
class ModelComments
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="modelComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="modelComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var ModelComments
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModelComments")
     * @ORM\JoinColumn(nullable=true)
     */
    private $upComments;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelCommentsPortfolio", mappedBy="modelComments", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $portfolioImages;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * ModelComments constructor
     */
    public function __construct()
    {
        $this->portfolioImages = new ArrayCollection();
        $this->enabled = false;
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
     * Set customer
     *
     * @param User $customer
     *
     * @return ModelComments
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return User
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set model
     *
     * @param Model $model
     *
     * @return ModelComments
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return ModelComments
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set upComments
     *
     * @param ModelComments $upComments
     *
     * @return ModelComments
     */
    public function setUpComments(ModelComments $upComments)
    {
        $this->upComments = $upComments;

        return $this;
    }

    /**
     * Get upComments
     *
     * @return ModelComments
     */
    public function getUpComments()
    {
        return $this->upComments;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return ModelComments
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get portfolio images
     *
     * @return ArrayCollection
     */
    public function getPortfolioImages()
    {
        return $this->portfolioImages;
    }

    /**
     * Add portfolio image
     *
     * @param ModelCommentsPortfolio $image
     */
    public function addPortfolioImage(ModelCommentsPortfolio $image)
    {
        $image->setModelComments($this);

        $this->portfolioImages[] = $image;
    }

    /**
     * Remove portfolio image
     *
     * @param ModelCommentsPortfolio $image
     */
    public function removePortfolioImage(ModelCommentsPortfolio $image)
    {
        $this->portfolioImages->removeElement($image);
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return ModelCommentsPortfolio
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
