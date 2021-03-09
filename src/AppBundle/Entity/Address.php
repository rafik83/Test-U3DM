<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity()
 */
class Address
{
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
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="street1", type="string", length=255)
     */
    private $street1;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=255, nullable=true)
     */
    private $street2;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=255)
     */
    private $zipcode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=255)
     */
    private $telephone;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Order", mappedBy="billingAddress", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $orderBil;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Order", mappedBy="shippingAddress", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $orderShi;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderModelUp", mappedBy="billingAddress", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $orderUpBil;


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
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Address
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Address
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set company
     *
     * @param string|null $company
     *
     * @return Address
     */
    public function setCompany($company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set street1
     *
     * @param string $street1
     *
     * @return Address
     */
    public function setStreet1($street1)
    {
        $this->street1 = $street1;

        return $this;
    }

    /**
     * Get street1
     *
     * @return string
     */
    public function getStreet1()
    {
        return $this->street1;
    }

    /**
     * Set street2
     *
     * @param string|null $street2
     *
     * @return Address
     */
    public function setStreet2($street2 = null)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * Get street2
     *
     * @return string|null
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     *
     * @return Address
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     *
     * @return Address
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Get address in a flat string: <company><street1><street2><zip><city>
     *
     * @return string
     */
    public function getFlatAddress()
    {
        $result = '';
        if (null !== $this->getCompany()) {
            $result .= $this->getCompany();
            $result .= ', ';
        }
        $result .= $this->getStreet1();
        if (null !== $this->getStreet2()) {
            $result .= ' ' . $this->getStreet2();
        }
        $result .= ' ' . $this->getZipcode() . ' ' . $this->getCity();
        return $result;
    }

    /**
     * Return true if the two addresses have the exact same values
     *
     * @param Address|null $address
     * @return bool
     */
    public function isEqualTo(Address $address = null)
    {
        if (null === $address) {
            return false;
        }
        return $this->getFirstname() === $address->getFirstname()
            && $this->getLastname()  === $address->getLastname()
            && $this->getCompany()   === $address->getCompany()
            && $this->getStreet1()   === $address->getStreet1()
            && $this->getStreet2()   === $address->getStreet2()
            && $this->getZipcode()   === $address->getZipcode()
            && $this->getCity()      === $address->getCity()
            && $this->getCountry()   === $address->getCountry()
            && $this->getTelephone() === $address->getTelephone();
    }
}
