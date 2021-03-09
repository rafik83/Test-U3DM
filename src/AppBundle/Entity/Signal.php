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
 * Signal
 *
 * @ORM\Table(name="reporting")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SignalRepository")
 * @Vich\Uploadable
 */
class Signal
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Statuses
     */
    const STATUS_CUSTUMER_SIGNAL    = 1; // customer signal a model
    const STATUS_VALID              = 2; // customer signal is ok
    const STATUS_UNVALID            = 3; // customer signal is not ok
    const STATUS_FINISHED           = 4; // signal corrected by customer

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="modelSignal")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var SignalRef
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SignalRef")
     * @ORM\JoinColumn(nullable=false)
     */
    private $signalRef;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * 
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * Signal constructor
     */
    public function __construct()
    {
        $this->status = self::STATUS_CUSTUMER_SIGNAL;
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
     * Set model
     *
     * @param Model $model
     *
     * @return Signal
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return Signal
     */
    public function getModel()
    {
        return $this->model;
    }

   

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Signal
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return Signal
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Signal
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set signalRef
     *
     * @param SignalRef $signalRef
     *
     * @return Signal
     */
    public function setSignalRef(SignalRef $signalRef)
    {
        $this->signalRef = $signalRef;

        return $this;
    }

    /**
     * Get signalRef
     *
     * @return Signal
     */
    public function getSignalRef()
    {
        return $this->signalRef;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return Signal
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

    /**
     * Set status
     *
     * @param int $status
     *
     * @return Signal
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

}
