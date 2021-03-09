<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Maker
 *
 * @ORM\Table(name="maker")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MakerRepository")
 * @Vich\Uploadable()
 */
class Maker
{
    /**
     * Hook timestampable behavior
     * Updates createdAt and updatedAt fields
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
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="company_type", type="string", length=255, nullable=true)
     */
    private $companyType;

   /**
     * @var string
     *
     * @ORM\Column(name="web_site", type="string", length=255, nullable=true)
     */
    private $webSite;

    /**
     * @var string
     *
     * @ORM\Column(name="siren", type="string", length=255, nullable=true)
     */
    private $siren;

    /**
     * @var string
     *
     * @ORM\Column(name="vat_number", type="string", length=255, nullable=true)
     */
    private $vatNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_id", type="string", length=255, nullable=true)
     */
    private $stripeId;

     /**
     * @var string
     *
     * @ORM\Column(name="stripe_representative_id", type="string", length=255, nullable=true)
     */
    private $stripeRepresentativeId;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_bank_account_id", type="string", length=255, nullable=true)
     */
    private $stripeBankAccountId;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_bank_account_iban_last4", type="string", length=255, nullable=true)
     */
    private $stripeBankAccountIbanLast4;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_bank_account_iban_bank_name", type="string", length=255, nullable=true)
     */
    private $stripeBankAccountIbanBankName;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $address;

    /**
     * @var bool
     *
     * @ORM\Column(name="printer", type="boolean")
     */
    private $printer;

    /**
     * @var bool
     *
     * @ORM\Column(name="designer", type="boolean")
     */
    private $designer;

    /**
     * @var bool
     *
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(name="blacklisted", type="boolean", length=255, options={"default":0})
     */
    private $blacklisted=0;


    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", inversedBy="maker")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Printer", mappedBy="maker", cascade={"persist", "remove"})
     */
    private $printers;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Scanner", mappedBy="maker", cascade={"persist", "remove"})
     */
    private $scanners;

    /**
     * @var bool
     *
     * @ORM\Column(name="pickup", type="boolean")
     */
    private $pickup;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $pickupAddress;

    /**
     * @var float
     *
     * @ORM\Column(name="custom_commission_rate", type="float", nullable=true)
     */
    private $customCommissionRate;

    /**
     * @var string
     *
     * @ORM\Column(name="bio", type="text", nullable=true)
     */
    private $bio;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="maker_profile", fileNameProperty="profilePictureName")
     */
    private $profilePictureFile;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_picture_name", type="string", length=255, nullable=true)
     */
    private $profilePictureName;

    /**
     * @var \DateTime
     * @ORM\Column(name="birth_date", type="datetime", nullable=true)
     */
    private $birthDate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MakerPortfolioImage", mappedBy="maker", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $portfolioImages;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="identity_paper", fileNameProperty="identityPaperName")
     */
    private $identityPaperFile;

    /**
     * @var string
     *
     * @ORM\Column(name="identity_paper_name", type="string", length=255, nullable=true)
     */
    private $identityPaperName;

    /**
     * @var string
     *
     * @ORM\Column(name="identity_paper_stripe_id", type="string", length=255, nullable=true)
     */
    private $identityPaperStripeId;

/**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="identity_paper", fileNameProperty="identityPaperNameVerso")
     */
    private $identityPaperFileVerso;

    /**
     * @var string
     *
     * @ORM\Column(name="identity_paper_name_verso", type="string", length=255, nullable=true)
     */
    private $identityPaperNameVerso;

    /**
     * @var string
     *
     * @ORM\Column(name="identity_paper_verso_stripe_id", type="string", length=255, nullable=true)
     */
    private $identityPaperVersoStripeId;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Rating", cascade={"remove"}, mappedBy="maker", orphanRemoval=true)
     */
    private $ratings;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ProjectType")
     * @ORM\JoinTable(name="maker_design_project_type")
     */
    private $designProjectTypes;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Skill")
     * @ORM\JoinTable(name="maker_design_skill")
     */
    private $designSkills;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Software")
     * @ORM\JoinTable(name="maker_design_software")
     */
    private $designSoftwares;

    /**
     * @var bool
     *
     * @ORM\Column(name="design_auto_moderation", type="boolean")
     */
    private $designAutoModeration;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Quotation", mappedBy="maker", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $quotations;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Order", mappedBy="maker")
     */
    private $orders;


    /**
     * User constructor
     */
    public function __construct()
    {
        $this->printer = false;
        $this->designer = false;
        $this->available = false;
        $this->enabled = false;
        $this->printers = new ArrayCollection();
        $this->scanners = new ArrayCollection();
        $this->portfolioImages = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->designProjectTypes = new ArrayCollection();
        $this->designSkills = new ArrayCollection();
        $this->designSoftwares = new ArrayCollection();
        $this->designAutoModeration = false;
        $this->quotations = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->pickup = false;
        $this->blacklisted=0;
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
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Maker
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Maker
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set company
     *
     * @param string|null $company
     *
     * @return Maker
     */
    public function setCompany($company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set company type
     *
     * @param string|null $companyType
     *
     * @return Maker
     */
    public function setCompanyType($companyType = null)
    {
        $this->companyType = $companyType;

        return $this;
    }

    /**
     * Get company type
     *
     * @return string|null
     */
    public function getCompanyType()
    {
        return $this->companyType;
    }

  /**
     * Set Web Site
     *
     * @param string|null $webSite
     *
     * @return Maker
     */
    public function setWebSite($webSite = null)
    {
        if (strtoupper(substr($webSite,0,4)) != 'HTTP') {
            $webSite = 'http://'.$webSite;
        }
        
        $this->webSite = $webSite;

        return $this;
    }

    /**
     * Get Web Site
     *
     * @return string|null
     */
    public function getWebSite()
    {
        return $this->webSite;
    }


    /**
     * Set siren
     *
     * @param string|null $siren
     *
     * @return Maker
     */
    public function setSiren($siren = null)
    {
        $this->siren = $siren;

        return $this;
    }

    /**
     * Get siren
     *
     * @return string|null
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * Set vat number
     *
     * @param string|null $vatNumber
     *
     * @return Maker
     */
    public function setVatNumber($vatNumber = null)
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * Get vat number
     *
     * @return string|null
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * Set address
     *
     * @param Address|null $address
     *
     * @return Maker
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get stripe_id
     *
     * @return string
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * Set stripe_id
     *
     * @param string $stripeId
     *
     * @return Maker
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * Set printer
     *
     * @param boolean $printer
     *
     * @return Maker
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;

        return $this;
    }

    /**
     * Is printer
     *
     * @return bool
     */
    public function isPrinter()
    {
        return $this->printer;
    }

    /**
     * Set designer
     *
     * @param boolean $designer
     *
     * @return Maker
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;

        return $this;
    }

    /**
     * Is designer
     *
     * @return bool
     */
    public function isDesigner()
    {
        return $this->designer;
    }

    /**
     * Set available
     *
     * @param boolean $available
     *
     * @return Maker
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Maker
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

  /**
     * Set blacklisted
     *
     * @param boolean $blacklisted
     *
     * @return Maker
     */
    public function setBlacklisted($blacklisted)
    {
        $this->blacklisted = $blacklisted;

        return $this;
    }

    /**
     * Is blacklisted
     *
     * @return bool
     */
    public function isBlacklisted()
    {
        return $this->blacklisted;
    }




    /**
     * Set user
     *
     * @param User $user
     *
     * @return Maker
     */
    public function setUser(User $user)
    {
        $user->setMaker($this);

        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get printers
     *
     * @return ArrayCollection
     */
    public function getPrinters()
    {
        return $this->printers;
    }

    /**
     * Add printer
     *
     * @param Printer $printer
     */
    public function addPrinter(Printer $printer)
    {
        $printer->setMaker($this);

        $this->printers[] = $printer;
    }

    /**
     * Remove printer
     *
     * @param Printer $printer
     */
    public function removePrinter(Printer $printer)
    {
        $this->printers->removeElement($printer);
    }

    /**
     * Get scanners
     *
     * @return ArrayCollection
     */
    public function getScanners()
    {
        return $this->scanners;
    }

    /**
     * Add scanner
     *
     * @param Scanner $scanner
     */
    public function addScanner(Scanner $scanner)
    {
        $scanner->setMaker($this);

        $this->scanners[] = $scanner;
    }

    /**
     * Remove scanner
     *
     * @param Scanner $scanner
     */
    public function removeScanner(Scanner $scanner)
    {
        $this->scanners->removeElement($scanner);
    }

    /**
     * Set pickup
     *
     * @param boolean $pickup
     *
     * @return Maker
     */
    public function setPickup($pickup)
    {
        $this->pickup = $pickup;

        return $this;
    }

    /**
     * Has pickup
     *
     * @return bool
     */
    public function hasPickup()
    {
        return $this->pickup;
    }

    /**
     * Set pickup address
     *
     * @param Address|null $address
     *
     * @return Maker
     */
    public function setPickupAddress($address = null)
    {
        $this->pickupAddress = $address;

        return $this;
    }

    /**
     * Get pickup address
     *
     * @return Address|null
     */
    public function getPickupAddress()
    {
        return $this->pickupAddress;
    }

    /**
     * Set custom commission rate, in percent
     *
     * @param float|null $rate
     *
     * @return Maker
     */
    public function setCustomCommissionRate($rate = null)
    {
        $this->customCommissionRate = $rate;

        return $this;
    }

    /**
     * Get custom commission rate, in percent
     *
     * @return float|null
     */
    public function getCustomCommissionRate()
    {
        return $this->customCommissionRate;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        if (null !== $this->getCompany()) {
            return $this->getCompany();
        } else {
            return $this->getFirstname() . ' ' . $this->getLastname();
        }
    }

    /**
     * Set bio
     *
     * @param string|null $bio
     * @return Maker
     */
    public function setBio($bio = null)
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Get bio
     *
     * @return string|null
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * Get the maker average rating
     *
     * @return float|null
     */
    public function getRating()
    {
        $sum = 0;
        $average = 0;
        $ratings = $this->getRatings();
        foreach ($ratings as $rating) {
            if($rating->getEnabled()){
                $sum += $rating->getRate();
            }
        }
        if($sum)
            $average = round($sum / count($ratings),1);

        return $average;

    }

    /**
     * Get the number of productions the maker made
     *
     * @return int
     */
    public function getNumberOfProductions()
    {
        // TODO
        return 0;
    }

    /**
     * Set stripeBankAccountId.
     *
     * @param string|null $stripeBankAccountId
     *
     * @return Maker
     */
    public function setStripeBankAccountId($stripeBankAccountId = null)
    {
        $this->stripeBankAccountId = $stripeBankAccountId;

        return $this;
    }

    /**
     * Get stripeBankAccountId.
     *
     * @return string|null
     */
    public function getStripeBankAccountId()
    {
        return $this->stripeBankAccountId;
    }


  /**
     * Set stripeRepresentativeId.
     *
     * @param string|null $stripeRepresentativeId
     *
     * @return Maker
     */
    public function setStripeRepresentativeId($stripeRepresentativeId = null)
    {
        $this->stripeRepresentativeId = $stripeRepresentativeId;

        return $this;
    }

    /**
     * Get stripeRepresentativeId.
     *
     * @return string|null
     */
    public function getStripeRepresentativeId()
    {
        return $this->stripeRepresentativeId;
    }


    /**
     * Set stripeBankAccountIbanLast4.
     *
     * @param string|null $stripeBankAccountIbanLast4
     *
     * @return Maker
     */
    public function setStripeBankAccountIbanLast4($stripeBankAccountIbanLast4 = null)
    {
        $this->stripeBankAccountIbanLast4 = $stripeBankAccountIbanLast4;

        return $this;
    }

    /**
     * Get stripeBankAccountIbanLast4.
     *
     * @return string|null
     */
    public function getStripeBankAccountIbanLast4()
    {
        return $this->stripeBankAccountIbanLast4;
    }

    /**
     * Set stripeBankAccountIbanBankName.
     *
     * @param string|null $stripeBankAccountIbanBankName
     *
     * @return Maker
     */
    public function setStripeBankAccountIbanBankName($stripeBankAccountIbanBankName = null)
    {
        $this->stripeBankAccountIbanBankName = $stripeBankAccountIbanBankName;

        return $this;
    }

    /**
     * Get stripeBankAccountIbanBankName.
     *
     * @return string|null
     */
    public function getStripeBankAccountIbanBankName()
    {
        return $this->stripeBankAccountIbanBankName;
    }

    /**
     * Set profile picture file
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $image
     *
     * @return Maker
     */
    public function setProfilePictureFile($image = null)
    {
        $this->profilePictureFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get profile picture file
     *
     * @return File|null
     */
    public function getProfilePictureFile()
    {
        return $this->profilePictureFile;
    }

    /**
     * Set profile picture name
     *
     * @param string|null $pictureName
     *
     * @return Maker
     */
    public function setProfilePictureName($pictureName = null)
    {
        $this->profilePictureName = $pictureName;

        return $this;
    }

    /**
     * Get profile picture name
     *
     * @return string|null
     */
    public function getProfilePictureName()
    {
        return $this->profilePictureName;
    }

    /**
     * Get printer.
     *
     * @return bool
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Get designer.
     *
     * @return bool
     */
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * Get available.
     *
     * @return bool
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get pickup.
     *
     * @return bool
     */
    public function getPickup()
    {
        return $this->pickup;
    }

    /**
     * Set birthDate.
     *
     * @param \DateTime|null $birthDate
     *
     * @return Maker
     */
    public function setBirthDate($birthDate = null)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate.
     *
     * @return \DateTime|null
     */
    public function getBirthDate()
    {
        return $this->birthDate;
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
     * @param MakerPortfolioImage $image
     */
    public function addPortfolioImage(MakerPortfolioImage $image)
    {
        $image->setMaker($this);

        $this->portfolioImages[] = $image;
    }

    /**
     * Remove portfolio image
     *
     * @param MakerPortfolioImage $image
     */
    public function removePortfolioImage(MakerPortfolioImage $image)
    {
        $this->portfolioImages->removeElement($image);
    }

    /**
     * Set identityPaperName.
     *
     * @param string|null $identityPaperName
     *
     * @return Maker
     */
    public function setIdentityPaperName($identityPaperName = null)
    {
        $this->identityPaperName = $identityPaperName;

        return $this;
    }

    /**
     * Get identityPaperName.
     *
     * @return string|null
     */
    public function getIdentityPaperName()
    {
        return $this->identityPaperName;
    }

    /**
     * Set identity paper file
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $image
     *
     * @return Maker
     */
    public function setIdentityPaperFile($image = null)
    {
        $this->identityPaperFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get identity paper file
     *
     * @return File|null
     */
    public function getIdentityPaperFile()
    {
        return $this->identityPaperFile;
    }

    /**
     * Set identityPaperStripeId.
     *
     * @param string|null $identityPaperStripeId
     *
     * @return Maker
     */
    public function setIdentityPaperStripeId($identityPaperStripeId = null)
    {
        $this->identityPaperStripeId = $identityPaperStripeId;

        return $this;
    }

    /**
     * Get identityPaperStripeId.
     *
     * @return string|null
     */
    public function getIdentityPaperStripeId()
    {
        return $this->identityPaperStripeId;
    }

 /**
     * Set identityPaperNameVerso.
     *
     * @param string|null $identityPaperNameVerso
     *
     * @return Maker
     */
    public function setIdentityPaperNameVerso($identityPaperNameVerso = null)
    {
        $this->identityPaperNameVerso = $identityPaperNameVerso;

        return $this;
    }

    /**
     * Get identityPaperNameVerso.
     *
     * @return string|null
     */
    public function getIdentityPaperNameVerso()
    {
        return $this->identityPaperNameVerso;
    }

    /**
     * Set identity paper file verso
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $image
     *
     * @return Maker
     */
    public function setIdentityPaperFileVerso($image = null)
    {
        $this->identityPaperFileVerso = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get identity paper file verso
     *
     * @return File|null
     */
    public function getIdentityPaperFileVerso()
    {
        return $this->identityPaperFileVerso;
    }

    /**
     * Set identityPaperVersoStripeId.
     *
     * @param string|null $identityPaperVersoStripeId
     *
     * @return Maker
     */
    public function setIdentityPaperVersoStripeId($identityPaperVersoStripeId = null)
    {
        $this->identityPaperVersoStripeId = $identityPaperVersoStripeId;

        return $this;
    }

    /**
     * Get identityPaperVersoStripeId.
     *
     * @return string|null
     */
    public function getIdentityPaperVersoStripeId()
    {
        return $this->identityPaperVersoStripeId;
    }


    /**
     * Add rating
     *
     * @param Rating $rating
     *
     * @return Maker
     */
    public function addRating(Rating $rating)
    {
        $rating->setMaker($this);

        $this->ratings[] = $rating;

        return $this;
    }

    /**
     * Remove rating
     *
     * @param Rating $rating
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRating(Rating $rating)
    {
        return $this->ratings->removeElement($rating);
    }

    /**
     * Get ratings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * Get ratings moderate
     *
     * @return Rating[]
     */
    public function getRatingsModerate()
    {

        $ratings = $this->getRatings();
        $ratingsModerate = [];
        foreach ($ratings as $rating) {
            if($rating->getEnabled()){
                array_push($ratingsModerate, $rating);
            }
        }
        return $ratingsModerate;
    }

    /**
     * Get design project types
     *
     * @return ArrayCollection
     */
    public function getDesignProjectTypes()
    {
        return $this->designProjectTypes;
    }

    /**
     * Add a design project type
     *
     * @param ProjectType $projectType
     *
     * @return Maker
     */
    public function addDesignProjectType(ProjectType $projectType)
    {
        $this->designProjectTypes[] = $projectType;

        return $this;
    }

    /**
     * Remove a design project type
     *
     * @param ProjectType $projectType
     */
    public function removeDesignProjectType(ProjectType $projectType)
    {
        $this->designProjectTypes->removeElement($projectType);
    }

    /**
     * Get design skills
     *
     * @return ArrayCollection
     */
    public function getDesignSkills()
    {
        return $this->designSkills;
    }

    /**
     * Add a design skill
     *
     * @param Skill $skill
     *
     * @return Maker
     */
    public function addDesignSkill(Skill $skill)
    {
        $this->designSkills[] = $skill;

        return $this;
    }

    /**
     * Remove a design skill
     *
     * @param Skill $skill
     */
    public function removeDesignSkill(Skill $skill)
    {
        $this->designSkills->removeElement($skill);
    }

    /**
     * Get design softwares
     *
     * @return ArrayCollection
     */
    public function getDesignSoftwares()
    {
        return $this->designSoftwares;
    }

    /**
     * Add a design software
     *
     * @param Software $software
     *
     * @return Maker
     */
    public function addDesignSoftware(Software $software)
    {
        $this->designSoftwares[] = $software;

        return $this;
    }

    /**
     * Remove a design software
     *
     * @param Software $software
     */
    public function removeDesignSoftware(Software $software)
    {
        $this->designSoftwares->removeElement($software);
    }

    /**
     * Set design auto moderation
     *
     * @param boolean $autoModeration
     *
     * @return Maker
     */
    public function setDesignAutoModeration($autoModeration)
    {
        $this->designAutoModeration = $autoModeration;

        return $this;
    }

    /**
     * Has design auto moderation
     *
     * @return bool
     */
    public function hasDesignAutoModeration()
    {
        return $this->designAutoModeration;
    }

    /**
     * Get quotations
     *
     * @return ArrayCollection
     */
    public function getQuotations()
    {
        return $this->quotations;
    }

    /**
     * Add a quotation
     *
     * @param Quotation $quotation
     */
    public function addQuotation(Quotation $quotation)
    {
        $this->quotations[] = $quotation;
    }

    /**
     * Remove a quotation
     *
     * @param Quotation $quotation
     */
    public function removeQuotation(Quotation $quotation)
    {
        $this->quotations->removeElement($quotation);
    }

    /**
     * Should scanner for this maker
     *
     * @return bool
     */
    public function shouldScanner()
    {
        $shouldScanner = false;

        $projectTypes = $this->getDesignProjectTypes();

        foreach ($projectTypes as $project) {
            if($project->isScanner()){
                $shouldScanner = true;
            }
        }

        return $shouldScanner;
    }

    /**
     * Get designAutoModeration.
     *
     * @return bool
     */
    public function getDesignAutoModeration()
    {
        return $this->designAutoModeration;
    }

    /**
     * Add order.
     *
     * @param Order $order
     *
     * @return Maker
     */
    public function addOrder(Order $order)
    {
        $order->setMaker($this);

        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param Order $order
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOrder(Order $order)
    {
        return $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * DeleteMaker
     *
     * @return Maker
     */
    public function deleteMaker()
    {
        $this->setEnabled(false);
        $this->setBlacklisted(true);
        $this->setAvailable(false);
    

        return $this;
    }
}
