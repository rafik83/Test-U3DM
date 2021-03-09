<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="ref_layer")
 * @ORM\Entity()
 * @Vich\Uploadable()
 */
class Layer
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
     * @ORM\Column(name="height", type="integer")
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="definition", type="string", length=255, nullable=true)
     */
    private $definition;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="ref_image", fileNameProperty="imageName")
     */
    private $imageFile;

    /**
     * @var string
     *
     * @ORM\Column(name="image_name", type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $editorialLink;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;


    /**
     * Layer constructor
     */
    public function __construct()
    {
        $this->enabled = true;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set height
     *
     * @param int $height
     *
     * @return Layer
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get height with its unit
     *
     * @return string
     */
    public function getHeightWithUnit()
    {
        return $this->getHeight() . ' µm';
    }

    /**
     * Set definition
     *
     * @param string|null $definition
     *
     * @return Layer
     */
    public function setDefinition($definition = null)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get definition
     *
     * @return string|null
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return Layer
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set image file
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $image
     *
     * @return Layer
     */
    public function setImageFile($image = null)
    {
        $this->imageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get image file
     *
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set image name
     *
     * @param string|null $imageName
     *
     * @return Layer
     */
    public function setImageName($imageName = null)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get image name
     *
     * @return string|null
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set editorial link
     *
     * @param string|null $link
     *
     * @return Layer
     */
    public function setEditorialLink($link = null)
    {
        $this->editorialLink = $link;

        return $this;
    }

    /**
     * Get editorial link
     *
     * @return string|null
     */
    public function getEditorialLink()
    {
        return $this->editorialLink;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return Layer
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
     * Set updatedAt
     *
     * @param \DateTime|null $updatedAt
     *
     * @return Layer
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
