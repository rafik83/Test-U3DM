<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="`orderModelUp`")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderModelUpRepository")
 */
class OrderModelUp
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

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
    const STATUS_MODEL_BUY        = 15;
    const STATUS_MODEL_NOT_PAID   = 21;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="ordersModels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Address", inversedBy="orderUpBil", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $billingAddress;


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
     * @var float
     *
     * @ORM\Column(name="commission_rate", type="float")
     */
    private $commissionRate;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=100, unique=true)
     */
    private $reference;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelBuy", mappedBy="orderModelUp", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * Get total tax amount
     *
     * @return int : amount in cents
     */
    public function getTotalTaxAmount()
    {
        return $this->getTotalAmountTaxIncl() - $this->getTotalAmountTaxExcl();
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
                'customer' => 'Livrée',
                'maker'    => 'Expédiée',
                'admin'    => 'Livrée'
            ),
            self::STATUS_PND => array(
                'customer' => 'Pli non distribué',
                'maker'    => 'Expédiée',
                'admin'    => 'PND'
            ),
            self::STATUS_CLOSED => array(
                'customer' => 'Livrée',
                'maker'    => 'Expédiée',
                'admin'    => 'Clôturée'
            ),
            self::STATUS_READY_FOR_PICKUP => array(
                'customer' => 'Disponible chez l\'imprimeur',
                'maker'    => 'En attente de retrait',
                'admin'    => 'Disponible client'
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
            self::STATUS_MODEL_BUY => array(
                'customer' => 'Payé',
                'maker'    => 'Acheté',
                'admin'    => 'Acheté'
            ),
            self::STATUS_MODEL_NOT_PAID => array(
                'customer' => 'Payement annulé',
                'maker'    => 'Payement annulé',
                'admin'    => 'Payement annulé'
            )
        );
        return $readableStatuses[$this->getStatus()][$context];
    }

}
