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
 * Model
 *
 * @ORM\Table(name="model")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelRepository")
 * @Vich\Uploadable
 */
class Model
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
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Maker")
     * @ORM\JoinColumn(nullable=false)
     */
    private $maker;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=191)
     */
    private $name;

    /**
     * @var int : price as cents
     *
     * @ORM\Column(name="price_tax_incl", type="integer")
     */
    private $priceTaxIncl;

    /**
     * @var int : price as cents
     *
     * @ORM\Column(name="price_tax_excl", type="integer")
     * 
     */
    private $priceTaxExcl;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var text
     *
     * @ORM\Column(name="caracteristique", type="text", nullable=true)
     */
    private $caracteristique;

    /**
     * @var string
     *
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModelLicense")
     * @ORM\JoinColumn(nullable=false)
     */
    private $licences;

    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="text")
     */
    private $tags;

    /**
     * @var ModelStatus
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModelStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="model_attachment", fileNameProperty="attachmentName", originalName="attachmentOriginalName")
     */
    private $attachmentFile;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_name", type="string", length=255, nullable=true)
     */
    private $attachmentName;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_name_original", type="string", length=255, nullable=true)
     */
    private $attachmentOriginalName;
    /**
     * @var int
     *
     * @ORM\Column(name="love", type="integer")
     */
    private $love;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_download", type="integer")
     */
    private $nb_download;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CategoryModel", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $categoryModel;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderModelBasketItem", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $orderModelBasket;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelPortfolioImage", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $portfolioImages;

    /**
     * @var string
     *
     */
    private $imageLink;

    /**
     * @var string
     *
     * @ORM\Column(name="correction_reason", type="text", nullable=true)
     */
    private $correctionReason;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelBuy", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelBuy;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelComments", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelComments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelLove", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelLove;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelDownload", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelDownload;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Signal", mappedBy="model", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelSignal;


    /**
     * Model constructor
     */
    public function __construct()
    {
        $this->love = 0;
        /*
        $this->priceTaxIncl = 0;
        $this->priceTaxExcl = 0;*/
        $this->nb_download = 0;
        $this->categoryModel = new ArrayCollection();
        $this->portfolioImages = new ArrayCollection();
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
     * Set maker
     *
     * @param Maker $maker
     *
     * @return Model
     */
    public function setMaker(Maker $maker)
    {
        $this->maker = $maker;

        return $this;
    }

    /**
     * Get maker
     *
     * @return Maker
     */
    public function getMaker()
    {
        return $this->maker;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Model
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set priceTaxIncl.
     *
     * @param int $priceTaxIncl
     *
     * @return Model
     */
    public function setPriceTaxIncl($priceTaxIncl)
    {
        $this->priceTaxIncl = $priceTaxIncl;

        return $this;
    }

    /**
     * Get priceTaxIncl.
     *
     * @return int
     */
    public function getPriceTaxIncl()
    {
        return $this->priceTaxIncl;
    }

    /**
     * Set priceTaxExcl.
     *
     * @param int $priceTaxExcl
     *
     * @return Model
     */
    public function setPriceTaxExcl($priceTaxExcl)
    {
        $this->priceTaxExcl = $priceTaxExcl;

        return $this;
    }

    /**
     * Get priceTaxExcl.
     *
     * @return int
     */
    public function getPriceTaxExcl()
    {
        return $this->priceTaxExcl;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Model
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
     * Set caracteristique.
     *
     * @param string $caracteristique
     *
     * @return Model
     */
    public function setCaracteristique($caracteristique)
    {
        $this->caracteristique = $caracteristique;

        return $this;
    }

    /**
     * Get caracteristique.
     *
     * @return text
     */
    public function getCaracteristique()
    {
        return $this->caracteristique;
    }

    /**
     * Set licences.
     *
     * @param string $licences
     *
     * @return Model
     */
    public function setLicences($licences)
    {
        $this->licences = $licences;

        return $this;
    }

    /**
     * Get licences.
     *
     * @return string
     */
    public function getLicences()
    {
        return $this->licences;
    }

    /**
     * Set tags.
     *
     * @param string $tags
     *
     * @return Model
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags.
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set status.
     *
     * @param ModelStatus $status
     *
     * @return Model
     */
    public function setstatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return ModelStatus
     */
    public function getstatus()
    {
        return $this->status;
    }

    /**
     * Set attachment file
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $file
     *
     * @return Model
     */
    public function setAttachmentFile($file = null)
    {
        $this->attachmentFile = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get attachment file
     *
     * @return File|null
     */
    public function getAttachmentFile()
    {
        return $this->attachmentFile;
    }

    /**
     * Set attachment name
     *
     * @param string|null $name
     *
     * @return Model
     */
    public function setAttachmentName($name = null)
    {
        $this->attachmentName = $name;

        return $this;
    }

    /**
     * Get attachment name
     *
     * @return string|null
     */
    public function getAttachmentName()
    {
        return $this->attachmentName;
    }

    /**
     * Set attachment original name
     *
     * @param string|null $name
     *
     * @return Model
     */
    public function setAttachmentOriginalName($name = null)
    {
        $this->attachmentOriginalName = $name;

        return $this;
    }

    /**
     * Get attachment original name
     *
     * @return string|null
     */
    public function getAttachmentOriginalName()
    {
        return $this->attachmentOriginalName;
    }

    /**
     * Set love.
     *
     * @param int $love
     *
     * @return Model
     */
    public function setLove($love)
    {
        $this->love = $love;

        return $this;
    }

    /**
     * Get love.
     *
     * @return int
     */
    public function getLove()
    {
        return $this->love;
    }
    
    /**
     * Set nb_download.
     *
     * @param int $nb_download
     *
     * @return Model
     */
    public function setNbDownload($nb_download)
    {
        $this->nb_download = $nb_download;

        return $this;
    }

    /**
     * Get nb_download.
     *
     * @return int
     */
    public function getNbDownload()
    {
        return $this->nb_download;
    }

    /**
     * Get product categoryModel
     *
     * @return ArrayCollection
     */
    public function getCategoryModel()
    {
        return $this->categoryModel;
    }

    /**
     * Add a product categoryModel
     *
     * @param CategoryModel $categoryModel
     *
     * @return Model
     */
    public function addCategoryModel(CategoryModel $categoryOfModel)
    {
        $categoryOfModel->setModel($this);

        $this->categoryModel[] = $categoryOfModel;

        return $this;
    }

    /**
     * Remove a product categoryModel
     *
     * @param CategoryModel $categoryModel
     */
    public function removeCategoryModel(CategoryModel $categoryOfModel)
    {
        $this->categoryModel->removeElement($categoryOfModel);
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
     * @param ModelPortfolioImage $image
     */
    public function addPortfolioImage(ModelPortfolioImage $image)
    {
        $image->setModel($this);

        $this->portfolioImages[] = $image;
    }

    /**
     * Remove portfolio image
     *
     * @param ModelPortfolioImage $image
     */
    public function removePortfolioImage(ModelPortfolioImage $image)
    {
        $this->portfolioImages->removeElement($image);
    }


    /**
     * Set imageLink.
     *
     * @param string $imageLink
     *
     * @return Model
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

    /**
     * Set correction reason
     *
     * @param string|null $reason
     *
     * @return Quotation
     */
    public function setCorrectionReason($reason = null)
    {
        $this->correctionReason = $reason;

        return $this;
    }

    /**
     * Get correction reason
     *
     * @return string|null
     */
    public function getCorrectionReason()
    {
        return $this->correctionReason;
    }

    /**
     * Get modelSignal signal
     *
     * @return ArrayCollection
     */
    public function getModelSignal()
    {
        return $this->modelSignal;
    }

    /**
     * Add modelSignal signal
     *
     * @param Signal $signal
     */
    public function addModelSignal(Signal $signal)
    {
        $signal->setModel($this);

        $this->modelSignal[] = $signal;
    }

    /**
     * Remove modelSignal signal
     *
     * @param Signal $signal
     */
    public function removeModelSignal(Signal $signal)
    {
        $this->modelSignal->removeElement($image);
    }
}
