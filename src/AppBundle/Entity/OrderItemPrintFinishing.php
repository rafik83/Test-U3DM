<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_item_print_finishing")
 * @ORM\Entity()
 */
class OrderItemPrintFinishing extends OrderItem
{
    /**
     * @var OrderItemPrint
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OrderItemPrint", inversedBy="itemFinishings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $printItem;

    /**
     * @var Finishing
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Finishing")
     * @ORM\JoinColumn(nullable=false)
     */
    private $finishing;


    /**
     * Set order print item
     *
     * @param OrderItemPrint $item
     * @return OrderItemPrintFinishing
     */
    public function setPrintItem(OrderItemPrint $item)
    {
        $this->printItem = $item;

        return $this;
    }

    /**
     * Get order print item
     *
     * @return OrderItemPrint
     */
    public function getPrintItem()
    {
        return $this->printItem;
    }

    /**
     * Set finishing
     *
     * @param Finishing $finishing
     * @return OrderItemPrintFinishing
     */
    public function setFinishing(Finishing $finishing)
    {
        $this->finishing = $finishing;

        return $this;
    }

    /**
     * Get finishing
     *
     * @return Finishing
     */
    public function getFinishing()
    {
        return $this->finishing;
    }

    /**
     * Implement abstract method
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Finition : ' . $this->getFinishing()->getName();
    }
}
