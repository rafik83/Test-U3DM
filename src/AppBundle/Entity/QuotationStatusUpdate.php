<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="quotation_status_update")
 * @ORM\Entity()
 */
class QuotationStatusUpdate
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
     * @var Quotation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quotation", inversedBy="statusUpdates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quotation;

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
     * Set quotation
     *
     * @param Quotation $quotation
     *
     * @return QuotationStatusUpdate
     */
    public function setQuotation(Quotation $quotation)
    {
        $this->quotation = $quotation;

        return $this;
    }

    /**
     * Get quotation
     *
     * @return Quotation
     */
    public function getQuotation()
    {
        return $this->quotation;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return QuotationStatusUpdate
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
     * @return QuotationStatusUpdate
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
}
