<?php

namespace AppBundle\Entity;

class Shipping
{
    /**
     * Constants
     */
    const TYPE_HOME_STANDARD = 'home_standard';
    const TYPE_HOME_EXPRESS  = 'home_express';
    const TYPE_RELAY         = 'relay';
    const TYPE_PICKUP        = 'pickup';
    const TYPE_MAKER_SHIP   = 'maker_shipment';
    const TYPE_NOT_SHIPPED   = 'not_shipped';

    public static function getReadableShippingType($type)
    {
        $result = '';
        switch ($type) {
            case self::TYPE_HOME_STANDARD:
                $result = 'À domicile - Standard';
                break;
            case self::TYPE_HOME_EXPRESS:
                $result = 'À domicile - Express';
                break;
            case self::TYPE_RELAY:
                $result = 'En point relais';
                break;
            case self::TYPE_PICKUP:
                $result = 'Retrait sur place';
                break;
            case self::TYPE_MAKER_SHIP:
                $result = 'Selon la commande';
                break;
            case self::TYPE_NOT_SHIPPED:
                $result = 'Non expédié';
                break;
        }
        return $result;
    }
}