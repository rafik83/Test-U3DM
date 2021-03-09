<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="ref_color")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ColorRepository")
 * @Vich\Uploadable()
 */
class Color
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
     * @ORM\Column(name="name_short", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_long", type="string", length=255, nullable=true)
     */
    private $nameLong;

    /**
     * @var string
     *
     * @ORM\Column(name="name_english", type="string", length=255, nullable=true)
     */
    private $nameEnglish;

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
     * @ORM\Column(name="hexadecimal_code", type="string", length=255)
     */
    private $hexadecimalCode;

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
     * Color constructor
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
     * Set name
     *
     * @param string $name
     *
     * @return Color
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set long name
     *
     * @param string|null $name
     *
     * @return Color
     */
    public function setNameLong($name = null)
    {
        $this->nameLong = $name;

        return $this;
    }

    /**
     * Get long name
     *
     * @return string|null
     */
    public function getNameLong()
    {
        return $this->nameLong;
    }

    /**
     * Set english name
     *
     * @param string|null $name
     *
     * @return Color
     */
    public function setNameEnglish($name)
    {
        $this->nameEnglish = $name;

        return $this;
    }

    /**
     * Get english name
     *
     * @return string|null
     */
    public function getNameEnglish()
    {
        return $this->nameEnglish;
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return Color
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
     * @return Color
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
     * @return Color
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
     * Set hexadecimal code
     *
     * @param string $code
     *
     * @return Color
     */
    public function setHexadecimalCode($code)
    {
        $this->hexadecimalCode = $code;

        return $this;
    }

    /**
     * Get hexadecimal code
     *
     * @return string
     */
    public function getHexadecimalCode()
    {
        return $this->hexadecimalCode;
    }

    /**
     * Set editorial link
     *
     * @param string|null $link
     *
     * @return Color
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
     * @return Color
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
     * @return Color
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
