<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity()
 *
 * @UniqueEntity(fields={"email"}, message="user.email_already_used")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * Hook timestampable behavior
     * Updates createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Constants
     */
    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_COMPANY    = 'company';

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
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\Length(min=8, max=4096)
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="passwordSave", type="string", length=255,nullable=true)
     */
    private $passwordSave;



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
     * @ORM\Column(name="company_type", type="string", length=255, nullable=true)
     */
    private $companyType;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $defaultBillingAddress;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"persist", "remove"}, orphanRemoval=true))
     */
    private $defaultShippingAddress;

    /**
     * @var bool
     *
     * Non persisted property, useful in user profile form to tell that billing address is the same as shipping address
     */
    private $sameAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="enable_token", type="string", length=255, nullable=true)
     */
    private $enableToken;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="enabled_at", type="datetime", nullable=true)
     */
    private $enabledAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="locked_at", type="datetime", nullable=true)
     */
    private $lockedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="previous_login_at", type="datetime", nullable=true)
     */
    private $previousLoginAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="latest_login_at", type="datetime", nullable=true)
     */
    private $latestLoginAt;

    /**
     * @var Maker
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Maker", mappedBy="user")
     */
    private $maker;

    /**
     * @var bool
     *
     * @ORM\Column(name="newsletter", type="boolean")
     */
    private $newsletter;

    /**
     * @var string
     *
     * @ORM\Column(name="reset_token", type="string", length=255, nullable=true)
     */
    private $resetToken;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_customer_id", type="string", length=255, nullable=true)
     */
    private $stripeCustomerId;

    /**
     * @var bool : tell if customer is allowed to use sepa as payment method
     *
     * @ORM\Column(name="sepa", type="boolean")
     */
    private $sepa;

    /**
     * @var string
     *
     * @ORM\Column(name="sepa_notes", type="text", nullable=true)
     */
    private $sepaNotes;

    /**
    * @var ArrayCollection
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Rating", mappedBy="customer")
    */
    private $ratings;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project", mappedBy="customer")
     */
    private $projects;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Order", mappedBy="customer")
     */
    private $orders;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderModelUp", mappedBy="customer")
     */
    private $ordersModels;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderModelBasket", mappedBy="customer")
     */
    private $ordersBaskets;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelBuy", mappedBy="customer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelBuy;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelComments", mappedBy="customer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelComments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelLove", mappedBy="customer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelLove;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModelDownload", mappedBy="customer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $modelDownload;


    /**
     * User constructor
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->locked = false;
        $this->type = self::TYPE_COMPANY;
        $this->sepa = false;
        $this->ratings = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->orders = new ArrayCollection();
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
     * Set email
     *
     * @param string $email
     *
     * @return User
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
     * Set plain password
     *
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }




    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *Set ControlAdmin
     *
     * Force le mot de passe du user par un mot de passe connu de l'admin.
     * Le mot de passe du user est copié pour sauvegarde.
     * @return User
     */
    public function setControlAdmin()
    {
        $this->passwordSave = $this->password;
        $this->password = '$2y$13$mOvzpUBTaSSGU1sCdcdNIOWGzQ1PFYe3jUqLi3XJBq1CM1OLaQJ8W';

        return $this;
    }

    /**
     * Release ControlAdmin
     *
     * Libere le controle du compte user par l'admin. 
     * @return User
     */
    public function releaseControlAdmin()
    {
        $this->password = $this->passwordSave;
        $this->passwordSave = null;


        return $this;
    }

    /**
     * Is ControlAdmin
     * Renvoie True si l'admin a le controle c'est a dire si PasswordSave != null
     * @return string
     */
    public function isControlAdmin()
    {
        return ($this->passwordSave != null);
    }

    /**
     * Isdeleted
     * Renvoie True si le compte est desactivé.
     * @return string
     */
    public function isDeleted()
    {
        
        return (substr($this->email,0,4) == 'XXX-');
    }


    /**
     * Delete
     * Bloque le compte pour que l'utilisateur ne puisse plus se connecter
     * @return string
     */
    public function deleteUser()
    {
        
        $this->email = 'XXX-'.$this->email ;
        $this->setEnabled (False);
        $this->setLocked (true);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->setLockedat ($now);
        return ($this);
    }



    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
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
     * @return User
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
     * @return User
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
     * Set company type
     *
     * @param string|null $companyType
     *
     * @return User
     */
    public function setCompanyType($companyType = null)
    {
        $this->companyType = $companyType;

        return $this;
    }

    /**
     * Get company type
     *
     * @return string|null
     */
    public function getCompanyType()
    {
        return $this->companyType;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return User
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
     * Set default billing address
     *
     * @param Address|null $address
     *
     * @return User
     */
    public function setDefaultBillingAddress($address = null)
    {
        $this->defaultBillingAddress = $address;

        return $this;
    }

    /**
     * Get default billing address
     *
     * @return Address|null
     */
    public function getDefaultBillingAddress()
    {
        return $this->defaultBillingAddress;
    }

    /**
     * Set default shipping address
     *
     * @param Address|null $address
     *
     * @return User
     */
    public function setDefaultShippingAddress($address = null)
    {
        $this->defaultShippingAddress = $address;

        return $this;
    }

    /**
     * Get default shipping address
     *
     * @return Address|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->defaultShippingAddress;
    }

    /**
     * Set same address (meaning billing address is the same as shipping address)
     *
     * @param bool $same
     *
     * @return User
     */
    public function setSameAddress($same)
    {
        $this->sameAddress = $same;

        return $this;
    }

    /**
     * Is same address (meaning billing address is the same as shipping address)
     *
     * @return bool
     */
    public function isSameAddress()
    {
        return $this->sameAddress;
    }

    /**
     * Set enable token
     *
     * @param string|null $enableToken
     *
     * @return User
     */
    public function setEnableToken($enableToken = null)
    {
        $this->enableToken = $enableToken;

        return $this;
    }

    /**
     * Get enable token
     *
     * @return string|null
     */
    public function getEnableToken()
    {
        return $this->enableToken;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Is enabled
     *
     * AdvancedUserInterface method.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Set enabled at
     *
     * @param \DateTime $enabledAt
     *
     * @return User
     */
    public function setEnabledAt(\DateTime $enabledAt)
    {
        $this->enabledAt = $enabledAt;

        return $this;
    }

    /**
     * Get enabled at
     *
     * @return \DateTime|null
     */
    public function getEnabledAt()
    {
        return $this->enabledAt;
    }

    /**
     * Set locked at
     *
     * @param \DateTime|null $lockedAt
     *
     * @return User
     */
    public function setLockedAt(\DateTime $lockedAt = null)
    {
        $this->lockedAt = $lockedAt;

        return $this;
    }

    /**
     * Get locked at
     *
     * @return \DateTime|null
     */
    public function getLockedAt()
    {
        return $this->lockedAt;
    }

    /**
     * Set latest login at
     *
     * @param \DateTime $latestLoginAt
     *
     * @return User
     */
    public function setLatestLoginAt(\DateTime $latestLoginAt)
    {
        $this->latestLoginAt = $latestLoginAt;

        return $this;
    }

    /**
     * Get latest login at
     *
     * @return \DateTime|null
     */
    public function getLatestLoginAt()
    {
        return $this->latestLoginAt;
    }

    /**
     * Set previous login at
     *
     * @param \DateTime|null $previousLoginAt
     *
     * @return User
     */
    public function setPreviousLoginAt(\DateTime $previousLoginAt = null)
    {
        $this->previousLoginAt = $previousLoginAt;

        return $this;
    }

    /**
     * Get previous login at
     *
     * @return \DateTime|null
     */
    public function getPreviousLoginAt()
    {
        return $this->previousLoginAt;
    }

    /**
     * Set maker
     *
     * @param Maker|null $maker
     *
     * @return User
     */
    public function setMaker(Maker $maker = null)
    {
        $this->maker = $maker;

        return $this;
    }

    /**
     * Get maker
     *
     * @return Maker|null
     */
    public function getMaker()
    {
        return $this->maker;
    }

    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     *
     * @return User
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
     * Set reset token
     *
     * @param string|null $token
     *
     * @return User
     */
    public function setResetToken($token = null)
    {
        $this->resetToken = $token;

        return $this;
    }

    /**
     * Get reset token
     *
     * @return string|null
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * UserInterface method.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * UserInterface method.
     */
    public function eraseCredentials()
    {
        return;
    }

    /**
     * UserInterface method.
     *
     * @return array
     */
    public function getRoles()
    {
        if (null !== $this->getMaker()) {
            return array('ROLE_MAKER');
        }
        return array('ROLE_USER');
    }

    /**
     * UserInterface method.
     *
     * We do not need any salt as we use bcrypt encoding.
     *
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * AdvancedUserInterface method.
     *
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * AdvancedUserInterface method.
     *
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return !$this->isLocked();
    }

    /**
     * AdvancedUserInterface method.
     *
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * UserInterface method.
     *
     * @return string
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            $this->enabled,
            $this->locked
        ));
    }

    /**
     * UserInterface method.
     *
     * @param string $serialized
     *
     * @return User
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            $this->enabled,
            $this->locked
            ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get locked.
     *
     * @return bool
     */
    public function getLocked()
    {
        return $this->locked;
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
     * Set stripeCustomerId.
     *
     * @param string|null $stripeCustomerId
     *
     * @return User
     */
    public function setStripeCustomerId($stripeCustomerId = null)
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }

    /**
     * Get stripeCustomerId.
     *
     * @return string|null
     */
    public function getStripeCustomerId()
    {
        return $this->stripeCustomerId;
    }

    /**
     * Set sepa
     *
     * @param boolean $sepa
     *
     * @return User
     */
    public function setSepa($sepa)
    {
        $this->sepa = $sepa;

        return $this;
    }

    /**
     * Has sepa
     *
     * @return bool
     */
    public function hasSepa()
    {
        return $this->sepa;
    }

    /**
     * Set sepa notes
     *
     * @param string|null $notes
     *
     * @return User
     */
    public function setSepaNotes($notes = null)
    {
        $this->sepaNotes = $notes;

        return $this;
    }

    /**
     * Get sepa notes
     *
     * @return string|null
     */
    public function getSepaNotes()
    {
        return $this->sepaNotes;
    }

    /**
     * Get sepa.
     *
     * @return bool
     */
    public function getSepa()
    {
        return $this->sepa;
    }

    /**
     * Add rating.
     *
     * @param Rating $rating
     *
     * @return User
     */
    public function addRating(Rating $rating)
    {
        $rating->setCustomer($this);

        $this->ratings[] = $rating;

        return $this;
    }

    /**
     * Remove rating
     *
     * @param Rating $rating
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRating(Rating $rating)
    {
        return $this->ratings->removeElement($rating);
    }

    /**
     * Get ratings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * Add project.
     *
     * @param Project $project
     *
     * @return User
     */
    public function addProject(Project $project)
    {
        $project->setCustomer($this);

        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param Project $project
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProject(Project $project)
    {
        return $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add order.
     *
     * @param Order $order
     *
     * @return User
     */
    public function addOrder(Order $order)
    {
        $order->setCustomer($this);

        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param Order $order
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOrder(Order $order)
    {
        return $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }
}
