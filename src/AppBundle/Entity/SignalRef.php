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
 * SignalRef
 *
 * @ORM\Table(name="signal_ref")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SignalRepository")
 * @Vich\Uploadable
 */
class SignalRef
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * 
     */
    private $signalName;

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
     * Set signalName
     *
     * @param string $signalName
     *
     * @return SignalRef
     */
    public function setSignalName($signalName)
    {
        $this->signalName = $signalName;

        return $this;
    }

    /**
     * Get signalName
     *
     * @return string
     */
    public function getSignalName()
    {
        return $this->signalName;
    }

}
