<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="order_file")
 * @ORM\Entity()
 */
class OrderFile
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
     * @var Order
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Order", inversedBy="file")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_original", type="string", length=255, nullable=true)
     */
    private $originalName;

        /**
     * @var string
     *
     * @ORM\Column(name="url_download", type="string", length=255, nullable=true)
     */
    private $urlDownload;

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
     * @return OrderFile
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
     * Set name
     *
     * @param string $name
     *
     * @return OrderFile
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
     * Set original name
     *
     * @param string|null $name
     *
     * @return OrderFile
     */
    public function setOriginalName($name = null)
    {
        $this->originalName = $name;

        return $this;
    }

    /**
     * Get original name
     *
     * @return string|null
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

 /**
     * Set url updownload
     *
     * @param string|null $name
     *
     * @return OrderFile
     */
    public function setUrlDownload($urlDownload = null)
    {
        $this->urlDownload = $urlDownload;

        return $this;
    }

    /**
     * Get URL download
     *
     * @return string|null
     */
    public function getUrlDownload()
    {
        return $this->urlDownload;
    }

}
