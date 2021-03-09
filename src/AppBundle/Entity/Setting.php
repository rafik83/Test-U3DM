<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="setting")
 * @ORM\Entity()
 */
class Setting
{
    /**
     * Settings keys
     */
    const DEFAULT_TAX_RATE        = 'default_tax_rate'; // float : percent (example : 19.6)
    const DEFAULT_COMMISSION_RATE = 'default_commission_rate'; // float : percent (example : 10.0)
    const FEE_PERCENT             = 'fee_percent';   // float : percent (example : 3.5)
    const FEE_THRESHOLD           = 'fee_threshold'; // int : amount in cents
    const FEE_AMOUNT              = 'fee_amount';    // int : amount in cents
    const SHIPPING_HOME_STANDARD  = 'shipping_home_standard'; // int : amount in cents
    const SHIPPING_HOME_EXPRESS   = 'shipping_home_express';  // int : amount in cents
    const SHIPPING_RELAY          = 'shipping_relay';         // int : amount in cents
    const DEFAULT_SHIPPING_WEIGHT = 'default_shipping_weight'; // float : weight in kg
    const DEFAULT_PRODUCTION_TIME = 'default_production_time'; // int : time in days
    const QUOTATION_AGREEMENT_TIME                    = 'quotation_agreement_time'; // int : time in days
    const ONE_WEEK_PROJECT_CLOSURE_TIME               = 'one_week_project_closure_time'; // int : time in days
    const FIFTEEN_DAYS_PROJECT_CLOSURE_TIME           = 'fifteen_days_project_closure_time'; // int : time in days
    const ONE_MONTH_PROJECT_CLOSURE_TIME              = 'one_month_project_closure_time'; // int : time in days
    const THREE_MONTHS_PROJECT_CLOSURE_TIME           = 'three_months_project_closure_time'; // int : time in days
    const MORE_THAN_THREE_MONTHS_PROJECT_CLOSURE_TIME = 'more_than_three_months_project_closure_time'; // int : time in days
    const MESSAGE_MODERATE_TEXT = 'message_moderate_text'; // string : text
    /**
     * Settings types
     */
    const TYPE_STRING  = 'string';
    const TYPE_INT     = 'int';
    const TYPE_FLOAT   = 'float';
    const TYPE_MONEY   = 'money';   // treated as int
    const TYPE_PERCENT = 'percent'; // treated as float

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
     * @ORM\Column(name="`key`", type="string", length=255, unique=true)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="string_value", type="string", length=255, nullable=true)
     */
    private $stringValue;

    /**
     * @var int
     *
     * @ORM\Column(name="int_value", type="integer", nullable=true)
     */
    private $intValue;

    /**
     * @var float
     *
     * @ORM\Column(name="float_value", type="float", nullable=true)
     */
    private $floatValue;


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
     * Set key
     *
     * @param string $key
     *
     * @return Setting
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Setting
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
     * Set name
     *
     * @param string $name
     *
     * @return Setting
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
     * Set string value
     *
     * @param string|null $value
     *
     * @return Setting
     */
    public function setStringValue($value = null)
    {
        $this->stringValue = $value;

        return $this;
    }

    /**
     * Get string value
     *
     * @return string|null
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * Set int value
     *
     * @param int|null $value
     *
     * @return Setting
     */
    public function setIntValue($value = null)
    {
        $this->intValue = $value;

        return $this;
    }

    /**
     * Get int value
     *
     * @return int|null
     */
    public function getIntValue()
    {
        return $this->intValue;
    }

    /**
     * Set float value
     *
     * @param float|null $value
     *
     * @return Setting
     */
    public function setFloatValue($value = null)
    {
        $this->floatValue = $value;

        return $this;
    }

    /**
     * Get float value
     *
     * @return float|null
     */
    public function getFloatValue()
    {
        return $this->floatValue;
    }

    /**
     * Get setting value
     *
     * @return string|int|float|null
     */
    public function getValue()
    {
        $value = null;
        switch ($this->getType()) {
            case self::TYPE_STRING:
                $value = $this->getStringValue();
                break;
            case self::TYPE_INT:
            case self::TYPE_MONEY:
                $value = $this->getIntValue();
                break;
            case self::TYPE_FLOAT:
            case self::TYPE_PERCENT:
                $value = $this->getFloatValue();
                break;
            default:
                $value = null;
                break;
        }
        return $value;
    }
}
