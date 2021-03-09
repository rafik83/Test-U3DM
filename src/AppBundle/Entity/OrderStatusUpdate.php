<?php

namespace AppBundle\Entity;

use AppBundle\Event\OrderEvent;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="order_status_update")
 * @ORM\Entity()
 */
class OrderStatusUpdate
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
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="statusUpdates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="origin", type="string", length=255)
     */
    private $origin;


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
     * @return OrderStatusUpdate
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
     * Set status
     *
     * @param int $status
     *
     * @return OrderStatusUpdate
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
     * Set origin
     *
     * @param string $origin
     *
     * @return OrderStatusUpdate
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Get admin readable status
     *
     * @see CDC v3.2 page 57
     * @return string
     */
    public function getReadableStatus()
    {
        $result = '';
        switch($this->getStatus()) {
            case Order::STATUS_AWAITING_PAYMENT:
                $result = 'Non payée';
                break;
            case Order::STATUS_NEW:
                $result = 'Paiement OK, en attente de prise en charge par le maker';
                break;
            case Order::STATUS_CANCELED:
                $result = 'Annulée';
                break;
            case Order::STATUS_REFUNDED:
                $result = 'Remboursée';
                break;
            case Order::STATUS_PROCESSING:
                $result = 'Prise en charge';
                break;
            case Order::STATUS_LABELED:
                $result = 'Étiquetée';
                break;
            case Order::STATUS_TRANSIT:
                $result = 'Remise au transporteur';
                break;
            case Order::STATUS_DELIVERED:
                $result = 'Livrée';
                break;
            case Order::STATUS_PND:
                $result = 'PND';
                break;
            case Order::STATUS_CLOSED:
                $result = 'Clôturée';
                break;
            case Order::STATUS_READY_FOR_PICKUP:
                $result = 'Disponible pour retrait';
                break;
            case Order::STATUS_AWAITING_SEPA:
                $result = 'En attente du virement';
                break;
            case Order::STATUS_REFUSED_SEPA:
                $result = 'Paiement SEPA refusé';
                break;
            case Order::STATUS_FILE_AVAILABLE:
                $result = 'Fichier disponible pour téléchargement';
                break;
            case Order::STATUS_FILE_DOWNLOADED:
                $result = 'Fichier téléchargé';
                break;
            case Order::STATUS_FILE_REJECTED:
                $result = 'Fichier rejeté';
                break;
            case Order::STATUS_FILE_VALIDATED:
                $result = 'Fichier validé';
                break;
            case Order::STATUS_MODEL_BUY:
                $result = 'Achetée';
                break;
            case Order::STATUS_MODEL_PAID:
                $result = 'Versement Maker';
                break;
            case Order::STATUS_MODEL_NOT_PAID:
                $result = 'Préparation Paiement';
                break;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getReadableOrigin()
    {
        $result = '';
        switch($this->getOrigin()) {
            case OrderEvent::ORIGIN_CUSTOMER:
                $result = 'client';
                break;
            case OrderEvent::ORIGIN_MAKER:
                $result = 'maker';
                break;
            case OrderEvent::ORIGIN_ADMIN:
                $result = 'admin';
                break;
            case OrderEvent::ORIGIN_SYSTEM:
                $result = 'système';
                break;
        }
        return $result;
    }
}
