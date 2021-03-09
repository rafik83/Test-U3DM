<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_item_design")
 * @ORM\Entity()
 */
class OrderItemDesign extends OrderItem
{
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;


    /**
     * Set description
     *
     * @param string $description
     *
     * @return OrderItemDesign
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Implement abstract method
     *
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}