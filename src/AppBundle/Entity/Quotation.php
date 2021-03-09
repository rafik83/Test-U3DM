<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Quotation
 *
 * @ORM\Table(name="quotation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuotationRepository")
 */
class Quotation
{
    /**
     * Constants
     */
    const STATUS_PENDING    = 1; // en attente
    const STATUS_PROCESSING = 2; // en cours
    const STATUS_REFUSED    = 3; // refusé
    const STATUS_SENT       = 4; // envoyé
    const STATUS_DISPATCHED = 5; // diffusé
    const STATUS_NOT_DISPATCHED = 8; // non diffusé
    const STATUS_ACCEPTED   = 6; // accepté
    const STATUS_DISCARDED  = 7; // non retenu
    const STATUS_CLOSED  = 9; // non retenu
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
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project", inversedBy="quotations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Maker", inversedBy="quotations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $maker;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\QuotationStatusUpdate", mappedBy="quotation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $statusUpdates;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=100, unique=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="internal_reference", type="string", length=255, nullable=true)
     */
    private $internalReference;

    /**
     * @var int
     *
     * @ORM\Column(name="production_time", type="integer")
     */
    private $productionTime;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\QuotationLine", mappedBy="quotation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"number" = "ASC"})
     */
    private $lines;

    /**
     * @var string
     *
     * @ORM\Column(name="correction_reason", type="text", nullable=true)
     */
    private $correctionReason;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="quotation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $messages;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Order", mappedBy="quotation")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $orders;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="saved_at", type="datetime", nullable=true)
     */
    private $savedAt;


    /**
     * User constructor
     */
    public function __construct()
    {
        $this->statusUpdates = new ArrayCollection();
        $this->lines = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->orders = new ArrayCollection();
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
     * Set project
     *
     * @param Project $project
     *
     * @return Quotation
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set maker
     *
     * @param Maker $maker
     *
     * @return Quotation
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
     * Set status
     *
     * @param int $status
     *
     * @return Quotation
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
     * @param QuotationStatusUpdate $update
     *
     * @return Quotation
     */
    public function addStatusUpdate(QuotationStatusUpdate $update)
    {
        $update->setQuotation($this);

        $this->statusUpdates[] = $update;

        return $this;
    }

    /**
     * Remove status update
     *
     * @param QuotationStatusUpdate $update
     */
    public function removeStatusUpdate(QuotationStatusUpdate $update)
    {
        $this->statusUpdates->removeElement($update);
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return Quotation
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
     * Set reference
     *
     * @param string $reference
     *
     * @return Quotation
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
     * Set production time
     *
     * @param int $productionTime
     *
     * @return Quotation
     */
    public function setProductionTime($productionTime)
    {
        $this->productionTime = $productionTime;

        return $this;
    }

    /**
     * Get production time
     *
     * @return int
     */
    public function getProductionTime()
    {
        return $this->productionTime;
    }

    /**
     * Get lines
     *
     * @return ArrayCollection
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Add line
     *
     * @param QuotationLine $line
     *
     * @return Quotation
     */
    public function addLine(QuotationLine $line)
    {
        $line->setQuotation($this);

        $this->lines[] = $line;

        return $this;
    }

    /**
     * Remove line
     *
     * @param QuotationLine $line
     */
    public function removeLine(QuotationLine $line)
    {
        $this->lines->removeElement($line);
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
            self::STATUS_PENDING => array(
                'customer' => 'En attente',
                'maker'    => 'En attente',
                'admin'    => 'En attente'
            ),
            self::STATUS_PROCESSING => array(
                'customer' => 'En cours',
                'maker'    => 'En cours',
                'admin'    => 'En cours'
            ),
            self::STATUS_REFUSED => array(
                'customer' => 'Refusé',
                'maker'    => 'Refusé',
                'admin'    => 'Refusé'
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
            self::STATUS_NOT_DISPATCHED => array(
                'customer' => 'Diffusé',
                'maker'    => 'Diffusé',
                'admin'    => 'Non Diffusé'
            ),
            self::STATUS_ACCEPTED => array(
                'customer' => 'Accepté',
                'maker'    => 'Accepté',
                'admin'    => 'Accepté'
            ),
            self::STATUS_DISCARDED => array(
                'customer' => 'Non retenu',
                'maker'    => 'Non retenu',
                'admin'    => 'Non retenu'
            ),
            self::STATUS_CLOSED => array(
                'customer' => 'Fermé',
                'maker'    => 'Fermé',
                'admin'    => 'Fermé'
            )
        );
        return $readableStatuses[$this->getStatus()][$context];
    }

    /**
     * Set internalReference.
     *
     * @param string $internalReference
     *
     * @return Quotation
     */
    public function setInternalReference($internalReference)
    {
        $this->internalReference = $internalReference;

        return $this;
    }

    /**
     * Get internalReference.
     *
     * @return string
     */
    public function getInternalReference()
    {
        return $this->internalReference;
    }

    /**
     * Get messages
     *
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add message
     *
     * @param Message $message
     *
     * @return Quotation
     */
    public function addMessage(Message $message)
    {
        $message->setQuotation($this);

        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param Message $message
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get orders
     *
     * @return ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add order
     *
     * @param Order $order
     *
     * @return Quotation
     */
    public function addOrder(Order $order)
    {
        $order->setQuotation($this);

        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param Order $order
     */
    public function removeOrder(Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get the first order
     *
     * @return Order|null
     */
    public function getOrder()
    {
        $result = null;
        if (0 < $this->getOrders()->count()) {
            $result = $this->getOrders()->first();
        }
        return $result;
    }

    /**
     * Set the date when the quotation has last been saved
     *
     * @param \DateTime|null $savedAt
     *
     * @return Quotation
     */
    public function setSavedAt(\DateTime $savedAt = null)
    {
        $this->savedAt = $savedAt;

        return $this;
    }

    /**
     * Get the date when the quotation has last been saved
     * Return the created date if saved date is null (for backward compatibility)
     *
     * @return \DateTime
     */
    public function getSavedAt()
    {
        $result = $this->savedAt;
        if (null === $result) {
            $result = $this->createdAt;
        }
        return $result;
    }

    /**
     * Get total lines prices
     *
     * @return Int
     */
    public function getTotalPrice()
    {
        
        $total = 0;

        foreach ($this->lines as $line) {
            
            $total += ($line->getPrice()*$line->getQuantity());

        }

        return $total;
    }

    /**
     * Get vat for total ht
     *
     * @return Int
     */
    public function getVatPrice()
    {
        
        $vat  = $this->getTotalPrice()*0.2;

        return $vat;
    }

}
