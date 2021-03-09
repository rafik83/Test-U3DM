<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModelDownload
 *
 * @ORM\Table(name="model_download")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelDownloadRepository")
 */
class ModelDownload
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="modelDownload")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="modelDownload")
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
     * @return ModelDownload
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return ModelDownload
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
     * @return ModelDownload
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return ModelDownload
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
     * @return ModelDownload
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
