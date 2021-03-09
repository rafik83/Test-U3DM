<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="model_comments_portfolio")
 * @ORM\Entity()
 * @Vich\Uploadable()
 */
class ModelCommentsPortfolio
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
     * @var ModelComments
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModelComments", inversedBy="portfolioImages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modelComments;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="comment_portfolio", fileNameProperty="pictureName")
     */
    private $pictureFile;

    /**
     * @var string
     *
     * @ORM\Column(name="picture_name", type="string", length=255, nullable=true)
     */
    private $pictureName;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var string
     *
     */
    private $imageLink;


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
     * Set modelComments
     *
     * @param ModelComments $modelComments
     *
     * @return ModelCommentsPortfolio
     */
    public function setModelComments(ModelComments $modelComments)
    {
        $this->modelComments = $modelComments;

        return $this;
    }

    /**
     * Get modelComments
     *
     * @return ModelComments
     */
    public function getModelComments()
    {
        return $this->modelComments;
    }

    /**
     * Set picture file
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $image
     *
     * @return ModelCommentsPortfolio
     */
    public function setPictureFile($image = null)
    {
        $this->pictureFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get picture file
     *
     * @return File|null
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * Set picture name
     *
     * @param string|null $pictureName
     *
     * @return ModelCommentsPortfolio
     */
    public function setPictureName($pictureName = null)
    {
        $this->pictureName = $pictureName;

        return $this;
    }

    /**
     * Get picture name
     *
     * @return string|null
     */
    public function getPictureName()
    {
        return $this->pictureName;
    }

    /**
     * Set position
     *
     * @param int $position
     *
     * @return ModelCommentsPortfolio
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function __toString() {
        return $this->pictureName;
    }

    /**
     * Set imageLink.
     *
     * @param string $imageLink
     *
     * @return ModelCommentsPortfolio
     */
    public function setImageLink($imageLink)
    {
        $this->imageLink = $imageLink;

        return $this;
    }

    /**
     * Get imageLink.
     *
     * @return string
     */
    public function getImageLink()
    {
        return $this->imageLink;
    }
}
