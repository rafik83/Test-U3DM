<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Embeddable\Dimensions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 */
class Project
{
    /**
     * Constants
     */
    const DELIVERY_ONE_WEEK               = 'one_week';
    const DELIVERY_FIFTEEN_DAYS           = 'fifteen_days';
    const DELIVERY_ONE_MONTH              = 'one_month';
    const DELIVERY_THREE_MONTHS           = 'three_months';
    const DELIVERY_MORE_THAN_THREE_MONTHS = 'more_than_three_months';

    const STATUS_CREATED    = 1;
    const STATUS_SENT       = 2;
    const STATUS_DISPATCHED = 3;
    const STATUS_CLOSED     = 4;
    const STATUS_DELETED    = 5;
    const STATUS_ORDERED    = 6;
    const STATUS_CANCEL     = 7;
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=100, unique=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProjectStatusUpdate", mappedBy="project", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $statusUpdates;

    /**
     * @var ProjectType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProjectType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Field")
     * @ORM\JoinTable(name="project_field")
     */
    private $fields;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Skill")
     * @ORM\JoinTable(name="project_skill")
     */
    private $skills;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Software")
     * @ORM\JoinTable(name="project_software")
     */
    private $softwares;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProjectFile", mappedBy="project", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $files;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_time", type="string", length=255)
     */
    private $deliveryTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_at", type="datetime")
     */
    private $closedAt;

    /**
     * @var Dimensions : in mm
     *
     * @ORM\Embedded(class="AppBundle\Entity\Embeddable\Dimensions", columnPrefix="dimensions_")
     */
    private $dimensions;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $scanAddress;

    /**
     * @var bool
     *
     * @ORM\Column(name="scan_on_site", type="boolean")
     */
    private $scanOnSite;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Quotation", mappedBy="project", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $quotations;

    /**
     * @var string
     *
     * @ORM\Column(name="deletion_reason", type="text", nullable=true)
     */
    private $deletionReason;

    /**
     * @var string
     *
     * @ORM\Column(name="return_reason", type="text", nullable=true)
     */
    private $returnReason;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->statusUpdates = new ArrayCollection();
        $this->fields = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->softwares = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->dimensions = new Dimensions();
        $this->scanOnSite = false;
        $this->quotations = new ArrayCollection();
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
     * Set customer
     *
     * @param User $user
     *
     * @return Project
     */
    public function setCustomer(User $user)
    {
        $this->customer = $user;

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
     * Set reference
     *
     * @param string $reference
     *
     * @return Project
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Project
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
     * Set status
     *
     * @param int $status
     *
     * @return Project
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

    /**
     * Get status updates
     *
     * @return ArrayCollection
     */
    public function getStatusUpdates()
    {
        return $this->statusUpdates;
    }

    /**
     * Add status update
     *
     * @param ProjectStatusUpdate $update
     *
     * @return Project
     */
    public function addStatusUpdate(ProjectStatusUpdate $update)
    {
        $update->setProject($this);

        $this->statusUpdates[] = $update;

        return $this;
    }

    /**
     * Remove status update
     *
     * @param ProjectStatusUpdate $update
     */
    public function removeStatusUpdate(ProjectStatusUpdate $update)
    {
        $this->statusUpdates->removeElement($update);
    }

    /**
     * Set project type
     *
     * @param ProjectType $type
     *
     * @return Project
     */
    public function setType(ProjectType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get project type
     *
     * @return ProjectType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get fields
     *
     * @return ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Add a field
     *
     * @param Field $field
     *
     * @return Project
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove a field
     *
     * @param Field $field
     */
    public function removeField(Field $field)
    {
        $this->fields->removeElement($field);
    }

    /**
     * Get skills
     *
     * @return ArrayCollection
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Add a skill
     *
     * @param Skill $skill
     *
     * @return Project
     */
    public function addSkill(Skill $skill)
    {
        $this->skills[] = $skill;

        return $this;
    }

    /**
     * Remove a skill
     *
     * @param Skill $skill
     */
    public function removeSkill(Skill $skill)
    {
        $this->skills->removeElement($skill);
    }

    /**
     * Get softwares
     *
     * @return ArrayCollection
     */
    public function getSoftwares()
    {
        return $this->softwares;
    }

    /**
     * Add a software
     *
     * @param Software $software
     *
     * @return Project
     */
    public function addSoftware(Software $software)
    {
        $this->softwares[] = $software;

        return $this;
    }

    /**
     * Remove a software
     *
     * @param Software $software
     */
    public function removeSoftware(Software $software)
    {
        $this->softwares->removeElement($software);
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get files
     *
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Add a file
     *
     * @param ProjectFile $file
     */
    public function addFile(ProjectFile $file)
    {
        $this->files[] = $file;
    }

    /**
     * Remove a file
     *
     * @param ProjectFile $file
     */
    public function removeFile(ProjectFile $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Set delivery time
     *
     * @param string $deliveryTime
     *
     * @return Project
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    /**
     * Get delivery time
     *
     * @return string
     */
    public function getDeliveryTime()
    {
        return $this->deliveryTime;
    }

    /**
     * Set closed at
     *
     * @param \DateTime $closedAt
     *
     * @return Project
     */
    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * Get closed at
     *
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * Set dimensions
     *
     * @param Dimensions $dimensions
     *
     * @return Project
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Get dimensions
     *
     * @return Dimensions
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Set scan address
     *
     * @param Address|null $address
     *
     * @return Project
     */
    public function setScanAddress($address = null)
    {
        $this->scanAddress = $address;

        return $this;
    }

    /**
     * Get scan address
     *
     * @return Address|null
     */
    public function getScanAddress()
    {
        return $this->scanAddress;
    }

    /**
     * Set scan on site
     *
     * @param boolean $scanOnSite
     *
     * @return Project
     */
    public function setScanOnSite($scanOnSite)
    {
        $this->scanOnSite = $scanOnSite;

        return $this;
    }

    /**
     * Is scan on site
     *
     * @return bool
     */
    public function isScanOnSite()
    {
        return $this->scanOnSite;
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
     * Get readable status, for the customer
     *
     * @return string
     */
    public function getCustomerReadableStatus()
    {
        return $this->getReadableStatus('customer');
    }

    /**
     * Get readable status, for the maker
     *
     * @return string
     */
    public function getMakerReadableStatus()
    {
        return $this->getReadableStatus('maker');
    }

    /**
     * Get readable status, for the administrator
     *
     * @return string
     */
    public function getAdminReadableStatus()
    {
        return $this->getReadableStatus('admin');
    }

    /**
     * @see CDC 
     * @param string $context : 'customer', 'maker', 'admin'
     * @return string
     */
    private function getReadableStatus($context)
    {
        if ('customer' !== $context && 'maker' !== $context && 'admin' !== $context) {
            return 'Inconnue';
        }

        $readableStatuses = array(
            self::STATUS_CREATED => array(
                'customer' => 'Créé',
                'maker'    => 'Créé',
                'admin'    => 'Créé'
            ),
            self::STATUS_SENT => array(
                'customer' => 'Envoyé',
                'maker'    => 'Envoyé',
                'admin'    => 'Envoyé'
            ),
            self::STATUS_DISPATCHED => array(
                'customer' => 'Diffusé',
                'maker'    => 'Diffusé',
                'admin'    => 'Diffusé'
            ),
            self::STATUS_CLOSED => array(
                'customer' => 'Clôturé',
                'maker'    => 'Clôturé',
                'admin'    => 'Clôturé'
            ),
            self::STATUS_DELETED => array(
                'customer' => 'Supprimé',
                'maker'    => 'Supprimé',
                'admin'    => 'Supprimé'
            ),
            self::STATUS_ORDERED => array(
                'customer' => 'Commandé',
                'maker'    => 'Commandé',
                'admin'    => 'Commandé'
            ),
            self::STATUS_CANCEL => array(
                'customer' => 'Fermé',
                'maker'    => 'Fermé',
                'admin'    => 'Abandonné'
            )
        );
        return $readableStatuses[$this->getStatus()][$context];
    }

    /**
     * @see CDC 
     * @return string
     */
    public function getReadableDeliveryTime()
    {

        $readableDeliveryTimes = array(
            self::DELIVERY_ONE_WEEK => array(
                '1 semaine'
            ),
            self::DELIVERY_FIFTEEN_DAYS => array(
                '15 jours'
            ),
            self::DELIVERY_ONE_MONTH => array(
                '1 mois'
            ),
            self::DELIVERY_THREE_MONTHS => array(
                '3 mois'
            ),
            self::DELIVERY_MORE_THAN_THREE_MONTHS => array(
                'Plus de 3 mois'
            )
        );
        return $readableDeliveryTimes[$this->getDeliveryTime()][0];
    }

    /**
     * Get scanOnSite.
     *
     * @return bool
     */
    public function getScanOnSite()
    {
        return $this->scanOnSite;
    }

    /**
     * Get nbQuotationForCustomer
     *
     * @return int
     */
    public function nbQuotationForCustomer()
    {
        $total = 0;

        foreach ($this->quotations as $quotation) {

            if($quotation->getStatus() == Quotation::STATUS_SENT || $quotation->getStatus() == Quotation::STATUS_DISPATCHED || $quotation->getStatus() == Quotation::STATUS_ACCEPTED || $quotation->getStatus() == Quotation::STATUS_DISCARDED){

                $total ++;

            }
            
        }

        return $total;
    }
    /**
     * Get nbQuotationProcessingForCustomer
     *
     * @return int
     */
    public function nbQuotationProcessingForCustomer()
    {
        $total = 0;

        foreach ($this->quotations as $quotation) {

            if( $quotation->getStatus() == Quotation::STATUS_PENDING || $quotation->getStatus() == Quotation::STATUS_PROCESSING ){

                $total ++;

            }
            
        }

        return $total;
    }


    /**
     * Get nbQuotationReceivedForCustomer
     *
     * @return int
     */
    public function nbQuotationReceivedForCustomer()
    {
        $total = 0;

        foreach ($this->quotations as $quotation) {

            if($quotation->getStatus() == Quotation::STATUS_DISPATCHED || $quotation->getStatus() == Quotation::STATUS_ACCEPTED || $quotation->getStatus() == Quotation::STATUS_DISCARDED){

                $total ++;

            }
            
        }

        return $total;
    }

    /**
     * Get nbModerationWaiting
     *
     * @return int
     */
    public function nbModerationWaiting()
    {
        $total = 0;

        foreach ($this->quotations as $quotation) {

            if($quotation->getStatus() == Quotation::STATUS_SENT){

                $total ++;

            }
            
        }

        return $total;
    }
    /**
     * Get nbNotDispatched
     *
     * @return int
     */
    public function nbNotDispatched()
    {
        $total = 0;

        foreach ($this->quotations as $quotation) {

            if($quotation->getStatus() == Quotation::STATUS_NOT_DISPATCHED || $quotation->getStatus() == Quotation::STATUS_REFUSED|| $quotation->getStatus() == Quotation::STATUS_CLOSED ){

                $total ++;

            }
            
        }

        return $total;
    }
    /**
     * Get RemainingDay
     * @return int
     */
    public function remainingDay(){

        $remainingDay = 0;

        $now = new \DateTime("now");
        $remainingDay = $now->diff($this->closedAt)->format('%R%a');

        return intVal($remainingDay);
    }

    /**
     * @return string
     */
    public function getReadableFields()
    {
        $fields = '';
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            if ('' !== $fields) {
                $fields .= ', ';
            }
            $fields .= $field->getName();
        }
        if ('' === $fields) {
            $fields = 'n/a';
        }
        return $fields;
    }

    /**
     * Get the first order we find in one of the project quotations
     *
     * @return Order|null
     */
    public function getOrder()
    {
        $result = null;
        $quotations = $this->getQuotations();
        foreach ($quotations as $quotation) {
            /** @var Quotation $quotation */
            $result = $quotation->getOrder();
            if (null !== $result) {
                break;
            }
        }
        return $result;
    }

    /**
     * Set deletion reason
     *
     * @param string|null $reason
     *
     * @return Project
     */
    public function setDeletionReason($reason = null)
    {
        $this->deletionReason = $reason;

        return $this;
    }

    /**
     * Get deletion reason
     *
     * @return string|null
     */
    public function getDeletionReason()
    {
        return $this->deletionReason;
    }

    /**
     * Set return reason
     *
     * @param string|null $reason
     *
     * @return Project
     */
    public function setReturnReason($reason = null)
    {
        $this->returnReason = $reason;

        return $this;
    }

    /**
     * Get return reason
     *
     * @return string|null
     */
    public function getReturnReason()
    {
        return $this->returnReason;
    }
}
