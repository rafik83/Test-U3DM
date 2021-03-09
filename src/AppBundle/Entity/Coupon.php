<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="coupon")
 * @ORM\Entity()
 */
class Coupon
{
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
     * @ORM\Column(name="code", type="string", length=50, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="float", nullable=true)
     */
    private $discountPercent;

    /**
     * @var int
     *
     * @ORM\Column(name="discount_amount", type="integer", nullable=true)
     */
    private $discountAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="min_order_amount", type="integer")
     */
    private $minOrderAmount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     */
    private $endDate;

    /**
     * @var int
     *
     * @ORM\Column(name="max_usage_per_customer", type="integer", nullable=true)
     */
    private $maxUsagePerCustomer;

    /**
     * @var int
     *
     * @ORM\Column(name="initial_stock", type="integer", nullable=true)
     */
    private $initialStock;

    /**
     * @var int
     *
     * @ORM\Column(name="remaining_stock", type="integer", nullable=true)
     */
    private $remainingStock;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User")
     */
    private $customers;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="launch_date", type="datetime")
     */
    private $launchDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var int
     *
     * @ORM\Column(name="u3dm_percent_part", type="integer")
     */
    private $u3dmPercentPart;

    /**
     * @var Administrator
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Administrator")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @var Administrator
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Administrator")
     */
    private $lastModifiedBy;


    /**
     * Coupon constructor
     */
    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->enabled = true;
        $this->u3dmPercentPart = 100;
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
     * Set code
     *
     * @param string $code
     *
     * @return Coupon
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Coupon
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Coupon
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Coupon
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
     * Set discount percent
     *
     * @param float|null $discountPercent
     *
     * @return Coupon
     */
    public function setDiscountPercent($discountPercent = null)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * Get discount percent
     *
     * @return float|null
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * Set discount amount
     *
     * @param int|null $discountAmount
     *
     * @return Coupon
     */
    public function setDiscountAmount($discountAmount = null)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * Get discount amount
     *
     * @return int|null
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * Set minimal order amount
     *
     * @param int $minOrderAmount
     *
     * @return Coupon
     */
    public function setMinOrderAmount($minOrderAmount)
    {
        $this->minOrderAmount = $minOrderAmount;

        return $this;
    }

    /**
     * Get minimal order amount
     *
     * @return int
     */
    public function getMinOrderAmount()
    {
        return $this->minOrderAmount;
    }

    /**
     * Set start date
     *
     * @param \DateTime $startDate
     *
     * @return Coupon
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get start date
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set end date
     *
     * @param \DateTime $endDate
     *
     * @return Coupon
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get end date
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set maximum usage per customer
     *
     * @param int|null $maxUsagePerCustomer
     *
     * @return Coupon
     */
    public function setMaxUsagePerCustomer($maxUsagePerCustomer = null)
    {
        $this->maxUsagePerCustomer = $maxUsagePerCustomer;

        return $this;
    }

    /**
     * Get maximum usage per customer
     *
     * @return int|null
     */
    public function getMaxUsagePerCustomer()
    {
        return $this->maxUsagePerCustomer;
    }

    /**
     * Set initial stock
     *
     * @param int|null $initialStock
     *
     * @return Coupon
     */
    public function setInitialStock($initialStock = null)
    {
        $this->initialStock = $initialStock;

        return $this;
    }

    /**
     * Get initial stock
     *
     * @return int|null
     */
    public function getInitialStock()
    {
        return $this->initialStock;
    }

    /**
     * Set remaining stock
     *
     * @param int|null $remainingStock
     *
     * @return Coupon
     */
    public function setRemainingStock($remainingStock = null)
    {
        $this->remainingStock = $remainingStock;

        return $this;
    }

    /**
     * Get remaining stock
     *
     * @return int|null
     */
    public function getRemainingStock()
    {
        return $this->remainingStock;
    }

    /**
     * Set launch date
     *
     * @param \DateTime $launchDate
     *
     * @return Coupon
     */
    public function setLaunchDate($launchDate)
    {
        $this->launchDate = $launchDate;

        return $this;
    }

    /**
     * Get launch date
     *
     * @return \DateTime
     */
    public function getLaunchDate()
    {
        return $this->launchDate;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return Coupon
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
     * Set U3DM percent part
     *
     * @param int $u3dmPercentPart
     *
     * @return Coupon
     */
    public function setU3dmPercentPart($u3dmPercentPart)
    {
        $this->u3dmPercentPart = $u3dmPercentPart;

        return $this;
    }

    /**
     * Get U3DM percent part
     *
     * @return int
     */
    public function getU3dmPercentPart()
    {
        return $this->u3dmPercentPart;
    }

    /**
     * Add customer
     *
     * @param User $customer
     *
     * @return Coupon
     */
    public function addCustomer(User $customer)
    {
        $this->customers[] = $customer;

        return $this;
    }

    /**
     * Remove customer
     *
     * @param User $customer
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCustomer(User $customer)
    {
        return $this->customers->removeElement($customer);
    }

    /**
     * Get customers
     *
     * @return ArrayCollection
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * Set created by
     *
     * @param Administrator $createdBy
     *
     * @return Coupon
     */
    public function setCreatedBy(Administrator $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get created by
     *
     * @return Administrator
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set last modified by
     *
     * @param Administrator|null $lastModifiedBy
     *
     * @return Coupon
     */
    public function setLastModifiedBy(Administrator $lastModifiedBy = null)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get last modified by
     *
     * @return Administrator|null
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
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
}
