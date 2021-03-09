<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModelLove
 *
 * @ORM\Table(name="model_love")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelLoveRepository")
 */
class ModelLove
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
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="modelLove")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="modelLove")
     * @ORM\JoinColumn(nullable=true)
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="sessionName", type="string", length=255, nullable=true)
     */
    private $sessionName;
    
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
     * @return ModelLove
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return ModelLove
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set customer
     *
     * @param User $customer
     *
     * @return ModelLove
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return ModelLove
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set sessionName.
     *
     * @param string $sessionName
     *
     * @return ModelLove
     */
    public function setSessionName($sessionName)
    {
        $this->sessionName = $sessionName;

        return $this;
    }

    /**
     * Get sessionName.
     *
     * @return string
     */
    public function getSessionName()
    {
        return $this->sessionName;
    }

}
