<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="order_shipment")
 * @ORM\Entity()
 */
class Shipment
{
    /**
     * Types
     */
    const TYPE_COLISSIMO  = 'colissimo';
    const TYPE_CHRONOPOST = 'chronopost';
    const TYPE_AUTRE = 'autre';

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="shipments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="parcel_number", type="string", length=255)
     */
    private $parcelNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="label_pdf_url", type="string", length=255)
     */
    private $labelPdfUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="tracking_maker_url", type="string",length=255, nullable=true)
     */
    private $trackingMakerUrl;



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
     * @return Shipment
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
     * Set type
     *
     * @param string $type
     *
     * @return Shipment
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
     * Set parcel number
     *
     * @param string $number
     *
     * @return Shipment
     */
    public function setParcelNumber($number)
    {
        $this->parcelNumber = $number;

        return $this;
    }

    /**
     * Get parcel number
     *
     * @return string
     */
    public function getParcelNumber()
    {
        return $this->parcelNumber;
    }

    /**
     * Set label PDF file URL
     *
     * @param string $url
     *
     * @return Shipment
     */
    public function setLabelPdfUrl($url)
    {
        $this->labelPdfUrl = $url;

        return $this;
    }

        /**
     * Set URL tracking Maker
     *
     * @param string $url
     *
     * @return Shipment
     */
    public function setTrackingMakerUrl($url)
    {
        $this->trackingMakerUrl = $url;

        return $this;
    }

    /**
     * Get  URL tracking Maker
     *
     * @return string
     */
    public function getTrackingMakerUrl()
    {
        return $this->trackingMakerUrl;
    }
    

    /**
     * Get label PDF file URL
     *
     * @return string
     */
    public function getLabelPdfUrl()
    {
        return $this->labelPdfUrl;
    }

    /**
     * @return string
     */
    public function getReadableType()
    {
        $result = '';
        switch($this->getType()) {
            case self::TYPE_COLISSIMO:
                $result = 'Colissimo';
                break;
            case self::TYPE_CHRONOPOST:
                $result = 'Chronopost';
                break;
            case self::TYPE_AUTRE:
                $result = 'Autre';
                break;
        }
        return $result;
    }

    /**
     * @return string|null
     */
    public function getTrackingUrl()
    {
        $result = null;
        switch($this->getType()) {
            case self::TYPE_COLISSIMO:
                $result = 'https://www.laposte.fr/particulier/outils/suivre-vos-envois?code=' . $this->getParcelNumber();
                break;
            case self::TYPE_CHRONOPOST:
                $result = 'https://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=' . $this->getParcelNumber();
                break;
            case self::TYPE_AUTRE:
                $result = $this->getTrackingMakerUrl();
                break;
        }
        return $result;
    }
}
