<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="order_payment")
 * @ORM\Entity()
 */
class Payment
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Constants
     */
    // @see https://stripe.com/docs/api/php#charge_object
    const CHARGE_STATUS_SUCCEEDED = 'succeeded';
    const CHARGE_STATUS_CONSUMED = 'consumed';
    const CHARGE_STATUS_PENDING   = 'pending';
    const CHARGE_STATUS_FAILED    = 'failed';
    const TYPE_CARD = 'card';
    const TYPE_SEPA = 'sepa';
    const TYPE_VIREMENT = 'virement';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_id", type="string", length=255)
     */
    private $chargeId;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_status", type="string", length=255)
     */
    private $chargeStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;


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
     * Set order
     *
     * @param Order $order
     *
     * @return Payment
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set amount, in cents
     *
     * @param int $amount
     *
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount, in cents
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set charge id
     *
     * @param string $chargeId
     *
     * @return Payment
     */
    public function setChargeId($chargeId)
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * Get charge id
     *
     * @return string
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }

    /**
     * Set charge status
     *
     * @param string $chargeStatus
     *
     * @return Payment
     */
    public function setChargeStatus($chargeStatus)
    {
        $this->chargeStatus = $chargeStatus;

        return $this;
    }

    /**
     * Get charge status
     *
     * @return string
     */
    public function getChargeStatus()
    {
        return $this->chargeStatus;
    }

    /**
     * Set type
     *
     * @param string|null $type
     *
     * @return Payment
     */
    public function setType($type = null)
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
        $type = $this->type;
        if (null === $type) {
            // default to card for backward compatibility
            $type = self::TYPE_CARD;
        }
        return $type;
    }

    /**
     * Get readable type
     *
     * @return string
     */
    public function getReadableType()
    {
        $result = 'Inconnu';
        switch ($this->getType()) {
            case self::TYPE_CARD:
                $result = 'Carte bancaire';
                break;
            case self::TYPE_SEPA:
                $result = 'SEPA';
                break;
            case self::TYPE_VIREMENT:
                $result = 'Virement';
                break;
        }
        return $result;
    }
}
