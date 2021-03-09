<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="`order`")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderRepository")
 */
class Order
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Types
     */
    const TYPE_PRINT  = 'print';
    const TYPE_DESIGN = 'design';

    /**
     * Statuses
     */
    const STATUS_AWAITING_PAYMENT = 1; // no payment
    const STATUS_NEW              = 2; // customer payment is ok
    const STATUS_CANCELED         = 3;
    const STATUS_REFUNDED         = 4;
    const STATUS_PROCESSING       = 5;
    const STATUS_LABELED          = 6; // shipping label has been printed
    const STATUS_TRANSIT          = 7;
    const STATUS_DELIVERED        = 8;
    const STATUS_PND              = 9; // could not be delivered
    const STATUS_CLOSED           = 11;
    const STATUS_READY_FOR_PICKUP = 12;
    const STATUS_AWAITING_SEPA    = 13;
    const STATUS_REFUSED_SEPA     = 14;
    const STATUS_FILE_AVAILABLE   = 15;
    const STATUS_FILE_DOWNLOADED  = 16;
    const STATUS_FILE_REJECTED    = 17;
    const STATUS_FILE_VALIDATED   = 18;
    const STATUS_MODEL_BUY        = 19;
    const STATUS_MODEL_PAID       = 20;
    const STATUS_MODEL_NOT_PAID    = 21;
    const STATUS_FILE_MODERATE_REJECTED    = 22;
    
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
     * @ORM\Column(name="reference", type="string", length=255, unique=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="order_token", type="string", length=255, nullable=true)
     */
    private $token;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Maker", inversedBy="orders")
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderStatusUpdate", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC", "id" = "DESC"})
     */
    private $statusUpdates;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Address", inversedBy="orderBil", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $billingAddress;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Address", inversedBy="orderShi", cascade={"persist", "remove"})
     */
    private $shippingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_type", type="string", length=255)
     */
    private $shippingType;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_relay_identifier", type="string", length=255, nullable=true)
     */
    private $shippingRelayIdentifier;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderItem", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $items;

    /**
     * @var int
     *
     * @ORM\Column(name="total_amount_tax_incl", type="integer")
     */
    private $totalAmountTaxIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="total_amount_tax_excl", type="integer")
     */
    private $totalAmountTaxExcl;

    /**
     * @var int
     *
     * @ORM\Column(name="production_amount_tax_incl", type="integer")
     */
    private $productionAmountTaxIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="production_amount_tax_excl", type="integer")
     */
    private $productionAmountTaxExcl;

    /**
     * @var int
     *
     * @ORM\Column(name="shipping_amount_tax_incl", type="integer")
     */
    private $shippingAmountTaxIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="shipping_amount_tax_excl", type="integer")
     */
    private $shippingAmountTaxExcl;

    /**
     * @var int
     *
     * @ORM\Column(name="fee_amount_tax_incl", type="integer")
     */
    private $feeAmountTaxIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="fee_amount_tax_excl", type="integer")
     */
    private $feeAmountTaxExcl;

    /**
     * @var int
     *
     * @ORM\Column(name="discount_amount_tax_incl", type="integer")
     */
    private $discountAmountTaxIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="discount_amount_tax_excl", type="integer")
     */
    private $discountAmountTaxExcl;

    /**
     * @var Coupon
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Coupon")
     */
    private $coupon;

    /**
     * @var float
     *
     * @ORM\Column(name="commission_rate", type="float")
     */
    private $commissionRate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Shipment", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $shipments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Payment", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $payments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Refund", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $refunds;

    /**
     * @var bool
     *
     * @ORM\Column(name="maker_paid", type="boolean")
     */
    private $makerPaid;

    /**
     * @var float
     *
     * @ORM\Column(name="default_tax_rate", type="float", nullable=true)
     */
    private $defaultTaxRate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $messages;

    /**
     * @var string
     *
     * @ORM\Column(name="instructions", type="text", nullable=true)
     */
    private $instructions;

    /**
     * @var Rating
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Rating", inversedBy="order")
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var Quotation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quotation", inversedBy="orders")
     */
    private $quotation;

    /**
     * @var OrderFile
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\OrderFile", mappedBy="order")
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="should_be_ready_at", type="datetime", nullable=true)
     */
    private $shouldBeReadyAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelBuy", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelBuy;


    /**
     * Order constructor
     */
    public function __construct()
    {
        $this->status = self::STATUS_AWAITING_PAYMENT;
        $this->statusUpdates = new ArrayCollection();
        $this->billingAddress = new Address();
        $this->items = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->refunds = new ArrayCollection();
        $this->makerPaid = false;
        $this->messages = new ArrayCollection();
        $this->discountAmountTaxIncl = 0;
        $this->discountAmountTaxExcl = 0;
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
     * Set reference
     *
     * @param string $reference
     *
     * @return Order
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
     * Set token
     *
     * @param string $token
     *
     * @return Order
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get Token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }





    /**
     * Set customer
     *
     * @param User $customer
     *
     * @return Order
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

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
     * Set maker
     *
     * @param Maker $maker
     *
     * @return Order
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
     * @return Order
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
     * @param OrderStatusUpdate $update
     *
     * @return Order
     */
    public function addStatusUpdate(OrderStatusUpdate $update)
    {
        $update->setOrder($this);

        $this->statusUpdates[] = $update;

        return $this;
    }

    /**
     * Remove status update
     *
     * @param OrderStatusUpdate $update
     */
    public function removeStatusUpdate(OrderStatusUpdate $update)
    {
        $this->statusUpdates->removeElement($update);
    }

    /**
     * Set billing address
     *
     * @param Address $address
     *
     * @return Order
     */
    public function setBillingAddress(Address $address)
    {
        $this->billingAddress = $address;

        return $this;
    }

    /**
     * Get billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Set shipping address
     *
     * @param Address|null $address
     *
     * @return Order
     */
    public function setShippingAddress(Address $address = null)
    {
        $this->shippingAddress = $address;

        return $this;
    }

    /**
     * Get shipping address
     *
     * @return Address|null
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Set shipping type
     *
     * @param string $type (as defined as constant in Setting entity class)
     *
     * @return Order
     */
    public function setShippingType($type)
    {
        $this->shippingType = $type;

        return $this;
    }

    /**
     * Get shipping type
     *
     * @return string
     */
    public function getShippingType()
    {
        return $this->shippingType;
    }

    /**
     * Set shipping relay identifier
     *
     * @param string|null $identifier
     *
     * @return Order
     */
    public function setShippingRelayIdentifier($identifier = null)
    {
        $this->shippingRelayIdentifier = $identifier;

        return $this;
    }

    /**
     * Get shipping relay identifier
     *
     * @return string|null
     */
    public function getShippingRelayIdentifier()
    {
        return $this->shippingRelayIdentifier;
    }

    /**
     * Get order items
     *
     * @return ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add order item
     *
     * @param OrderItem $item
     *
     * @return Order
     */
    public function addItem(OrderItem $item)
    {
        $item->setOrder($this);

        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove order item
     *
     * @param OrderItem $item
     */
    public function removeItem(OrderItem $item)
    {
        $this->items->removeElement($item);
    }

    /**
     * Set total amount tax inclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setTotalAmountTaxIncl($amount)
    {
        $this->totalAmountTaxIncl = $amount;

        return $this;
    }

    /**
     * Get total amount tax inclusive, in cents
     *
     * @return int
     */
    public function getTotalAmountTaxIncl()
    {
        return $this->totalAmountTaxIncl;
    }

    /**
     * Set total amount tax exclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setTotalAmountTaxExcl($amount)
    {
        $this->totalAmountTaxExcl = $amount;

        return $this;
    }

    /**
     * Get total amount tax exclusive, in cents
     *
     * @return int
     */
    public function getTotalAmountTaxExcl()
    {
        return $this->totalAmountTaxExcl;
    }

    /**
     * Set production amount tax inclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setProductionAmountTaxIncl($amount)
    {
        $this->productionAmountTaxIncl = $amount;

        return $this;
    }

    /**
     * Get production amount tax inclusive, in cents
     *
     * @return int
     */
    public function getProductionAmountTaxIncl()
    {
        return $this->productionAmountTaxIncl;
    }

    /**
     * Set production amount tax exclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setProductionAmountTaxExcl($amount)
    {
        $this->productionAmountTaxExcl = $amount;

        return $this;
    }

    /**
     * Get production amount tax exclusive, in cents
     *
     * @return int
     */
    public function getProductionAmountTaxExcl()
    {
        return $this->productionAmountTaxExcl;
    }

    /**
     * Set shipping amount tax inclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setShippingAmountTaxIncl($amount)
    {
        $this->shippingAmountTaxIncl = $amount;

        return $this;
    }

    /**
     * Get shipping amount tax inclusive, in cents
     *
     * @return int
     */
    public function getShippingAmountTaxIncl()
    {
        return $this->shippingAmountTaxIncl;
    }

    /**
     * Set shipping amount tax exclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setShippingAmountTaxExcl($amount)
    {
        $this->shippingAmountTaxExcl = $amount;

        return $this;
    }

    /**
     * Get shipping amount tax exclusive, in cents
     *
     * @return int
     */
    public function getShippingAmountTaxExcl()
    {
        return $this->shippingAmountTaxExcl;
    }

    /**
     * Set fee amount tax inclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setFeeAmountTaxIncl($amount)
    {
        $this->feeAmountTaxIncl = $amount;

        return $this;
    }

    /**
     * Get fee amount tax inclusive, in cents
     *
     * @return int
     */
    public function getFeeAmountTaxIncl()
    {
        return $this->feeAmountTaxIncl;
    }

    /**
     * Set fee amount tax exclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setFeeAmountTaxExcl($amount)
    {
        $this->feeAmountTaxExcl = $amount;

        return $this;
    }

    /**
     * Get fee amount tax exclusive, in cents
     *
     * @return int
     */
    public function getFeeAmountTaxExcl()
    {
        return $this->feeAmountTaxExcl;
    }

    /**
     * Set discount amount tax inclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setDiscountAmountTaxIncl($amount)
    {
        $this->discountAmountTaxIncl = $amount;

        return $this;
    }

    /**
     * Get discount amount tax inclusive, in cents
     *
     * @return int
     */
    public function getDiscountAmountTaxIncl()
    {
        return $this->discountAmountTaxIncl;
    }

    /**
     * Set discount amount tax exclusive, in cents
     *
     * @param int $amount
     *
     * @return Order
     */
    public function setDiscountAmountTaxExcl($amount)
    {
        $this->discountAmountTaxExcl = $amount;

        return $this;
    }

    /**
     * Get discount amount tax exclusive, in cents
     *
     * @return int
     */
    public function getDiscountAmountTaxExcl()
    {
        return $this->discountAmountTaxExcl;
    }

    /**
     * Set coupon
     *
     * @param Coupon|null $coupon
     *
     * @return Order
     */
    public function setCoupon(Coupon $coupon = null)
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * Get coupon
     *
     * @return Coupon|null
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * Set commission rate, in percent
     *
     * @param float $rate
     *
     * @return Order
     */
    public function setCommissionRate($rate)
    {
        $this->commissionRate = $rate;

        return $this;
    }

    /**
     * Get commission rate, in percent
     *
     * @return float
     */
    public function getCommissionRate()
    {
        return $this->commissionRate;
    }

    /**
     * Get maker cut amount, to be paid when order is closed
     *
     * @return int : amount in cents
     */
    public function getMakerCutAmountTaxIncl()
    {
        $baseAmount = $this->getProductionAmountTaxIncl();

        // handle coupon
        $baseAmount -= $this->getDiscountAmountForMakerTaxIncl();

        return (int)round(((100 - $this->getCommissionRate()) / 100) * $baseAmount);
    }

    public function getDiscountAmountForMakerTaxIncl()
    {
        return $this->getDiscountAmountForMaker(true);
    }

    public function getDiscountAmountForMakerTaxExcl()
    {
        return $this->getDiscountAmountForMaker(false);
    }

    /**
     * Get maker coupon part amount
     *
     * @param bool $taxIncl
     * @return int
     */
    private function getDiscountAmountForMaker($taxIncl = true)
    {
        $res = 0;
        if (null !== $this->getCoupon()) {
            $makerPercentPart = 100 - $this->getCoupon()->getU3dmPercentPart();
            if (0 < $makerPercentPart) {
                $base = $taxIncl ? $this->getDiscountAmountTaxIncl() : $this->getDiscountAmountTaxExcl();
                $res = round($base * $makerPercentPart / 100);
            }
        }
        return $res;
    }

    public function getTotalAmountForMakerTaxIncl()
    {
        return $this->getProductionAmountTaxIncl() - $this->getDiscountAmountForMakerTaxIncl();
    }

    /**
     * Get platform cut amount (= fee + shipping + commission on production = total - maker cut)
     *
     * @return int : amount in cents
     */
    public function getPlatformCutAmountTaxIncl()
    {
        return $this->getTotalAmountTaxIncl() - $this->getMakerCutAmountTaxIncl();
    }

    /**
     * Get shipments
     *
     * @return ArrayCollection
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * Add shipment
     *
     * @param Shipment $shipment
     *
     * @return Order
     */
    public function addShipment(Shipment $shipment)
    {
        $shipment->setOrder($this);

        $this->shipments[] = $shipment;

        return $this;
    }

    /**
     * Remove shipment
     *
     * @param Shipment $shipment
     */
    public function removeShipment(Shipment $shipment)
    {
        $this->shipments->removeElement($shipment);
    }

    /**
     * Get payments
     *
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Add payment
     *
     * @param Payment $payment
     *
     * @return Order
     */
    public function addPayment(Payment $payment)
    {
        $payment->setOrder($this);

        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param Payment $payment
     */
    public function removePayment(Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get refunds
     *
     * @return ArrayCollection
     */
    public function getRefunds()
    {
        return $this->refunds;
    }

    /**
     * Add refund
     *
     * @param Refund $refund
     *
     * @return Order
     */
    public function addRefund(Refund $refund)
    {
        $refund->setOrder($this);

        $this->refunds[] = $refund;

        return $this;
    }

    /**
     * Remove refund
     *
     * @param Refund $refund
     */
    public function removeRefund(Refund $refund)
    {
        $this->refunds->removeElement($refund);
    }

    /**
     * Get total tax amount
     *
     * @return int : amount in cents
     */
    public function getTotalTaxAmount()
    {
        return $this->getTotalAmountTaxIncl() - $this->getTotalAmountTaxExcl();
    }

    /**
     * Get production tax amount
     *
     * @return int : amount in cents
     */
    public function getProductionTaxAmount()
    {
        return $this->getProductionAmountTaxIncl() - $this->getProductionAmountTaxExcl();
    }

    /**
     * Set maker paid
     *
     * @param bool $makerPaid
     * @return Order
     */
    public function setMakerPaid($makerPaid)
    {
        $this->makerPaid = $makerPaid;

        return $this;
    }

    /**
     * Is maker paid
     *
     * @return bool
     */
    public function isMakerPaid()
    {
        return $this->makerPaid;
    }

    /**
     * Set default tax rate, in percent
     *
     * @param float|null $rate
     *
     * @return Order
     */
    public function setDefaultTaxRate($rate = null)
    {
        $this->defaultTaxRate = $rate;

        return $this;
    }

    /**
     * Get default tax rate, in percent
     *
     * @return float
     */
    public function getDefaultTaxRate()
    {
        $rate = $this->defaultTaxRate;
        if (null === $rate) {
            $rate = 20.0;
        }
        return $rate;
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
     * @return Order
     */
    public function addMessage(Message $message)
    {
        $message->setOrder($this);

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
     * Check if we can force the maker payment
     * Maker has to not be paid already, and the order must have one of the allowed statuses
     *
     * @return bool
     */
    public function isAllowedToManuallyPayTheMaker()
    {
        return (
            false === $this->isMakerPaid() && (
                   self::STATUS_TRANSIT === $this->getStatus()
                || self::STATUS_DELIVERED === $this->getStatus()
                || self::STATUS_PND === $this->getStatus()
                || self::STATUS_CLOSED === $this->getStatus()
                || self::STATUS_READY_FOR_PICKUP === $this->getStatus()
            )
        );
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
     * @see CDC v3.2 page 57
     * @param string $context : 'customer', 'maker', 'admin'
     * @return string
     */
    private function getReadableStatus($context)
    {
        if ('customer' !== $context && 'maker' !== $context && 'admin' !== $context) {
            return 'Inconnue';
        }
        $readableStatuses = array(
            self::STATUS_AWAITING_PAYMENT => array(
                'customer' => 'Non payée',
                'maker'    => 'Non payée', // not supposed to bee seen by makers
                'admin'    => 'Non payée'
            ),
            self::STATUS_NEW => array(
                'customer' => 'En attente',
                'maker'    => 'En attente',
                'admin'    => 'En attente'
            ),
            self::STATUS_CANCELED => array(
                'customer' => 'Annulée',
                'maker'    => 'Annulée',
                'admin'    => 'Annulée'
            ),
            self::STATUS_REFUNDED => array(
                'customer' => 'Remboursée',
                'maker'    => 'Annulée',
                'admin'    => 'Remboursée'
            ),
            self::STATUS_PROCESSING => array(
                'customer' => 'En cours d\'impression',
                'maker'    => 'Prise en charge',
                'admin'    => 'Prise en charge'
            ),
            self::STATUS_LABELED => array(
                'customer' => 'En cours d\'impression',
                'maker'    => 'Étiquetée',
                'admin'    => 'Étiquetée'
            ),
            self::STATUS_TRANSIT => array(
                'customer' => 'En cours de livraison',
                'maker'    => 'Expédiée',
                'admin'    => 'Remise au transporteur'
            ),
            self::STATUS_DELIVERED => array(
                'customer' => 'Donner votre avis',
                'maker'    => 'Livré',
                'admin'    => 'Livré'
            ),
            self::STATUS_PND => array(
                'customer' => 'Pli non distribué',
                'maker'    => 'Livré',
                'admin'    => 'PND'
            ),
            self::STATUS_CLOSED => array(
                'customer' => 'Cloturée',
                'maker'    => 'Livré',
                'admin'    => 'Clôturée'
            ),
            self::STATUS_READY_FOR_PICKUP => array(
                'customer' => 'Disponible chez le Maker',
                'maker'    => 'En attente de retrait',
                'admin'    => 'Disponible en retrait'
            ),
            self::STATUS_AWAITING_SEPA => array(
                'customer' => 'En attente du virement',
                'maker'    => 'En attente du virement', // not supposed to bee seen by makers
                'admin'    => 'En attente du virement ou paiement SEPA'
            ),
            self::STATUS_REFUSED_SEPA => array(
                'customer' => 'Virement refusé ou abandonné',
                'maker'    => 'Virement refusé ou abandonné', // not supposed to bee seen by makers
                'admin'    => 'Virement refusé ou abandonné'
            ),
            self::STATUS_FILE_AVAILABLE => array(
                'customer' => 'Fichier disponible. A valider ',
                'maker'    => 'En attente validation client',
                'admin'    => 'Fichier disponible '
            ),
            self::STATUS_FILE_DOWNLOADED => array(
                'customer' => 'Fichier à valider',
                'maker'    => 'En attente validation client',
                'admin'    => 'Fichier téléchargé'
            ),
            self::STATUS_FILE_MODERATE_REJECTED => array(
                'customer' => 'Fichier refusé - verification plateforme',
                'maker'    => 'En attente validation client',
                'admin'    => 'Fichier refusé - à vérifier'
            ),
            self::STATUS_FILE_REJECTED => array(
                'customer' => 'Fichier refusé',
                'maker'    => 'Fichier refusé',
                'admin'    => 'Fichier refusé'
            ),
            self::STATUS_FILE_VALIDATED => array(
                'customer' => 'Fichier validé',
                'maker'    => 'Fichier validé',
                'admin'    => 'Fichier validé'
            ),
            self::STATUS_MODEL_BUY => array(
                'customer' => 'Payé',
                'maker'    => 'Acheté',
                'admin'    => 'Acheté'
            ),
            self::STATUS_MODEL_PAID => array(
                'customer' => 'Payé',
                'maker'    => 'Acheté',
                'admin'    => 'Acheté'
            ),
            self::STATUS_MODEL_NOT_PAID => array(
                'customer' => 'Echec paiement',
                'maker'    => 'Echec paiement',
                'admin'    => 'Echec paiement'
            )
        );
        if (!array_key_exists($this->getStatus(), $readableStatuses)) {
            return '';
        }
        return $readableStatuses[$this->getStatus()][$context];
    }

    /**
     * Calculate the date when the order should be ready, depending on the production time, and ignoring week-ends.
     *
     * @param int $productionTime : number of working days
     * @param \DateTime $from : date to consider as starting point
     * @return \DateTime
     */
    public function getShouldBeReadyDate($productionTime = 2, $from = null)
    {
        $result = clone $this->getCreatedAt();
        if (null !== $from) {
            $result = clone $from;
        }
        $result->setTimezone(new \DateTimeZone('Europe/Paris'));

        // get order hour to decide how many working days we should add
        $workingDaysToAdd = $productionTime;
        $hour = (int)$result->format('G');
        $dayOfWeek = (int)$result->format('w');
        if ($hour >= 12 && $dayOfWeek !== 0 && $dayOfWeek !== 6) {
            // add one working day if order was placed after 12 on a working day
            $workingDaysToAdd++;
        }
        // always set hour to 17 in Paris, to avoid issue with near midnight orders
        // (this date hour is not used anyway, the day is the only important thing)
        $result->setTime(17, 0, 0);

        return $this->addWorkingDays($result, $workingDaysToAdd);
    }

    /**
     * Get the expected delivery date, by adding some working days to the "should be ready" date,
     * depending on the shipping type: 2 days if standard or relay, 1 day if express, 0 if not shipped
     *
     * @return \DateTime|null
     */
    public function getExpectedDeliveryDate()
    {
        if (null === $this->getShouldBeReadyAt()) {
            return null;
        }
        // add some working days to the "should be ready" date, depending on the shipping type
        $workingDaysToAdd = 2;// home standard and relay
        if (Shipping::TYPE_HOME_EXPRESS === $this->getShippingType()) {
            $workingDaysToAdd = 1;
        } elseif (Shipping::TYPE_NOT_SHIPPED === $this->getShippingType() || Shipping::TYPE_MAKER_SHIP === $this->getShippingType() ) {
            $workingDaysToAdd = 0;
        } elseif (Shipping::TYPE_PICKUP === $this->getShippingType()) {
            $workingDaysToAdd = 0;
        }
        $shouldBeReadyAt = clone $this->getShouldBeReadyAt();
        return $this->addWorkingDays($shouldBeReadyAt, $workingDaysToAdd);
    }

    /**
     * Helper method to add some working days to a given date.
     *
     * @param \DateTime $dateTime
     * @param $workingDaysToAdd
     * @return \DateTime
     */
    public function addWorkingDays(\DateTime $dateTime, $workingDaysToAdd)
    {
        $daysOffset = 0;// actual number of days to offset from the order date
        $currentDayOfWeek = (int)$dateTime->format('w'); // from 0 for Sunday to 6 for Saturday
        while ($workingDaysToAdd > 0) {
            if (6 !== $currentDayOfWeek && 0 !== $currentDayOfWeek) {
                // reduce the number of working days to add if we are not on the week-end
                $workingDaysToAdd--;
            }
            $daysOffset++;
            $currentDayOfWeek++;
            if (7 <= $currentDayOfWeek) {
                $currentDayOfWeek = 0;
            } elseif (6 === $currentDayOfWeek && 0 === $workingDaysToAdd) {
                // if we arrive on Saturday and there is no more working day to add, add 2 more days to offset
                $daysOffset += 2;
            }
        }

        // add actual days offset to order date
        $dateTime->add(new \DateInterval('P'.$daysOffset.'D'));

        // return modified date
        return $dateTime;
    }

    /**
     * Get makerPaid.
     *
     * @return bool
     */
    public function getMakerPaid()
    {
        return $this->makerPaid;
    }

    /**
     * Set instructions
     *
     * @param string|null $instructions
     *
     * @return Order
     */
    public function setInstructions($instructions = null)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Get instructions
     *
     * @return string|null
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Set rating
     *
     * @param Rating|null $rating
     *
     * @return Order
     */
    public function setRating(Rating $rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return Rating
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Order
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
     * Get readable type
     *
     * @return string
     */
    public function getReadableType()
    {
        $result = '';
        if (self::TYPE_PRINT === $this->getType()) {
            $result = 'Impression';
        } elseif (self::TYPE_DESIGN === $this->getType()) {
            $result = 'Design';
        } else {
            $result = 'Modèle';
        }
        return $result;
    }

    /**
     * Set quotation
     *
     * @param Quotation|null $quotation
     *
     * @return Order
     */
    public function setQuotation(Quotation $quotation = null)
    {
        $this->quotation = $quotation;

        return $this;
    }

    /**
     * Get quotation
     *
     * @return Quotation|null
     */
    public function getQuotation()
    {
        return $this->quotation;
    }

    /**
     * Set file
     *
     * @param OrderFile|null $orderFile
     *
     * @return Order
     */
    public function setFile(OrderFile $orderFile = null)
    {
        $this->file = $orderFile;

        return $this;
    }

    /**
     * Get file
     *
     * @return OrderFile|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get latest datetime when the order status changed to STATUS_FILE_DOWNLOADED
     *
     * @return \DateTime|null
     */
    public function getFileDownloadedAt()
    {
        $result = null;
        $statusUpdates = $this->getStatusUpdates();
        foreach($statusUpdates as $statusUpdate) {
            /** @var OrderStatusUpdate $statusUpdate */
            if (self::STATUS_FILE_DOWNLOADED === $statusUpdate->getStatus()) {
                $result = $statusUpdate->getCreatedAt();
                break;
            }
        }
        return $result;
    }

    /**
     * Get latest datetime when the order status changed to STATUS_FILE_AVAILABLE
     *
     * @return \DateTime|null
     */
    public function getFileAvailableAt()
    {
        $result = null;
        $statusUpdates = $this->getStatusUpdates();
        foreach($statusUpdates as $statusUpdate) {
            /** @var OrderStatusUpdate $statusUpdate */
            if (self::STATUS_FILE_AVAILABLE === $statusUpdate->getStatus()) {
                $result = $statusUpdate->getCreatedAt();
                break;
            }
        }
        return $result;
    }
    /**
     * Get latest datetime when the order status changed to STATUS_FILE_VALIDATED
     *
     * @return \DateTime|null
     */
    public function getFileAcceptedAt()
    {
        $result = null;
        $statusUpdates = $this->getStatusUpdates();
        foreach($statusUpdates as $statusUpdate) {
            /** @var OrderStatusUpdate $statusUpdate */
            if (self::STATUS_FILE_VALIDATED === $statusUpdate->getStatus()) {
                $result = $statusUpdate->getCreatedAt();
                break;
            }
        }
        return $result;
    }
    /**
     * Get latest datetime when the order status changed to STATUS_FILE_REJECTED
     *
     * @return \DateTime|null
     */
    public function getFileRejectedAt()
    {
        $result = null;
        $statusUpdates = $this->getStatusUpdates();
        foreach($statusUpdates as $statusUpdate) {
            /** @var OrderStatusUpdate $statusUpdate */
            if (self::STATUS_FILE_REJECTED === $statusUpdate->getStatus()) {
                $result = $statusUpdate->getCreatedAt();
                break;
            }
        }
        return $result;
    }

    /**
     * Get latest datetime when the order status changed to STATUS_DELIVERED
     *
     * @return \DateTime|null
     */
    public function getDeliveredAt()
    {
        $result = null;
        $statusUpdates = $this->getStatusUpdates();
        foreach($statusUpdates as $statusUpdate) {
            /** @var OrderStatusUpdate $statusUpdate */
            if (self::STATUS_DELIVERED === $statusUpdate->getStatus()) {
                $result = $statusUpdate->getCreatedAt();
                break;
            }
        }
        return $result;
    }

    /**
     * Set the date when the order should be ready at
     *
     * @param \DateTime|null $shouldBeReadyAt
     *
     * @return Order
     */
    public function setShouldBeReadyAt(\DateTime $shouldBeReadyAt = null)
    {
        $this->shouldBeReadyAt = $shouldBeReadyAt;

        return $this;
    }

    /**
     * Get the date when the order should be ready at
     *
     * @return \DateTime|null
     */
    public function getShouldBeReadyAt()
    {
        return $this->shouldBeReadyAt;
    }


    /**
     * @return string
     */
    public function getReadableShippingType()
    {
        $result = '';
        switch ($this->getShippingType()) {
            case Shipping::TYPE_HOME_STANDARD:
                $result = 'Domicile standard';
                break;
            case Shipping::TYPE_HOME_EXPRESS:
                $result = 'Domicile express';
                break;
            case Shipping::TYPE_RELAY:
                $result = 'Point relais';
                break;
            case Shipping::TYPE_PICKUP:
                $result = 'Retrait sur place';
                break;
            case Shipping::TYPE_MAKER_SHIP:
                $result = 'Selon la commande';
                break;
            case Shipping::TYPE_NOT_SHIPPED:
                $result = 'Numérique';
                break;
        }
        return $result;
    }
}
