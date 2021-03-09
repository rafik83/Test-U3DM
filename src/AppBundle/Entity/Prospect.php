<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="prospect")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProspectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Prospect
{
    /**
     * Constants
     */
    const CUSTOMER_TYPE_INDIVIDUAL = 'individual';
    const CUSTOMER_TYPE_COMPANY    = 'company';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(name="maker", type="boolean")
     */
    private $maker;

    /**
     * @var bool
     *
     * @ORM\Column(name="printer", type="boolean")
     */
    private $printer;

    /**
     * @var bool
     *
     * @ORM\Column(name="designer", type="boolean")
     */
    private $designer;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_type", type="string", nullable=true)
     */
    private $customerType;

    /**
     * @var bool
     *
     * @ORM\Column(name="newsletter", type="boolean")
     */
    private $newsletter;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="prospect_tag_technology")
     */
    private $technologies;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="prospect_tag_domain")
     */
    private $domains;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;


    /**
     * Prospect constructor
     */
    public function __construct()
    {
        $this->technologies = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->newsletter = false;
    }

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
     * Set created at
     *
     * @param \DateTime $createdAt
     *
     * @return Prospect
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Prospect
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstname
     *
     * @param string|null $firstname
     *
     * @return Prospect
     */
    public function setFirstname($firstname = null)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Prospect
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
     * Set company
     *
     * @param string|null $company
     *
     * @return Prospect
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
     * Set city
     *
     * @param string $city
     *
     * @return Prospect
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
     * @return Prospect
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
     * Set phone number
     *
     * @param string $phoneNumber
     *
     * @return Prospect
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phone number
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Prospect
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set maker
     *
     * @param boolean $maker
     *
     * @return Prospect
     */
    public function setMaker($maker)
    {
        $this->maker = $maker;

        return $this;
    }

    /**
     * Is maker
     *
     * @return bool
     */
    public function isMaker()
    {
        return $this->maker;
    }

    /**
     * Set printer
     *
     * @param boolean $printer
     *
     * @return Prospect
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;

        return $this;
    }

    /**
     * Is printer
     *
     * @return bool
     */
    public function isPrinter()
    {
        return $this->printer;
    }

    /**
     * Set designer
     *
     * @param boolean $designer
     *
     * @return Prospect
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;

        return $this;
    }

    /**
     * Is designer
     *
     * @return bool
     */
    public function isDesigner()
    {
        return $this->designer;
    }

    /**
     * Set customer type
     *
     * @param string|null $customerType
     *
     * @return Prospect
     */
    public function setCustomerType($customerType = null)
    {
        $this->customerType = $customerType;

        return $this;
    }

    /**
     * Get customer type
     *
     * @return string|null
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     *
     * @return Prospect
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Is newsletter
     *
     * @return bool
     */
    public function isNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Get domain tags
     *
     * @return ArrayCollection
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * Add a domain tag
     *
     * @param Tag $domain
     */
    public function addDomain(Tag $domain)
    {
        $this->domains[] = $domain;
    }

    /**
     * Remove a domain tag
     *
     * @param Tag $domain
     */
    public function removeDomain(Tag $domain)
    {
        $this->domains->removeElement($domain);
    }

    /**
     * Get technology tags
     *
     * @return ArrayCollection
     */
    public function getTechnologies()
    {
        return $this->technologies;
    }

    /**
     * Add a technology tag
     *
     * @param Tag $technology
     */
    public function addTechnology(Tag $technology)
    {
        $this->technologies[] = $technology;
    }

    /**
     * Remove a technology tag
     *
     * @param Tag $technology
     */
    public function removeTechnology(Tag $technology)
    {
        $this->technologies->removeElement($technology);
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        $result = '';
        if (null !== $this->getFirstname()) {
            $result .= $this->getFirstname() . ' ';
        }
        $result .= $this->getLastname();

        return $result;
    }

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get maker.
     *
     * @return bool
     */
    public function getMaker()
    {
        return $this->maker;
    }

    /**
     * Get printer.
     *
     * @return bool
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Get designer.
     *
     * @return bool
     */
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * Get newsletter.
     *
     * @return bool
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set token
     *
     * @param string|null $token
     *
     * @return Prospect
     */
    public function setToken($token = null)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }
}
