<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ref_project")
 * @ORM\Entity()
 */
class ProjectType
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="description_maker", type="text", nullable=true)
     */
    private $descriptionMaker;

    /**
     * @var bool
     *
     * @ORM\Column(name="scanner", type="boolean")
     */
    private $scanner;

    /**
     * @var bool
     *
     * @ORM\Column(name="shipping", type="boolean")
     */
    private $shipping;

    /**
     * @var bool
     *
     * @ORM\Column(name="file", type="boolean")
     */
    private $file;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(name="tagSpec", type="text", nullable=true)
     */
    private $tagSpec;

    /**
     * @var bool
     *
     * @ORM\Column(name="address_project", type="boolean", nullable=true)
     */
    private $addressProject;
    /**
     * @var bool
     *
     * @ORM\Column(name="address_project_label", type="text", nullable=true)
     */
    private $addressProjectLabel;
    /**
     * @var bool
     *
     * @ORM\Column(name="shipping_choice", type="boolean", nullable=true)
     */
    private $shippingChoice;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->scanner = false;
        $this->shipping = false;
        $this->file = false;
        $this->enabled = true;
        $this->shippingChoice = false;
        $this->addressProject = false;

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
     * Set name
     *
     * @param string $name
     *
     * @return ProjectType
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
     * Set description
     *
     * @param string|null $description
     *
     * @return ProjectType
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description for maker
     *
     * @param string|null $description
     *
     * @return ProjectType
     */
    public function setDescriptionMaker($description = null)
    {
        $this->descriptionMaker = $description;

        return $this;
    }

    /**
     * Get description for maker
     *
     * @return string|null
     */
    public function getDescriptionMaker()
    {
        return $this->descriptionMaker;
    }

    /**
     * Set scanner
     *
     * @param bool $scanner
     *
     * @return ProjectType
     */
    public function setScanner($scanner)
    {
        $this->scanner = $scanner;

        return $this;
    }

    /**
     * Get scanner
     *
     * @return bool
     */
    public function isScanner()
    {
        return $this->scanner;
    }

    /**
     * Set shipping
     *
     * @param bool $shipping
     *
     * @return ProjectType
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * Get shipping
     *
     * @return bool
     */
    public function isShipping()
    {
        return ($this->shipping == true);
    }

    /**
     * Set file
     *
     * @param bool $file
     *
     * @return ProjectType
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->file;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return ProjectType
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get scanner.
     *
     * @return bool
     */
    public function getScanner()
    {
        return $this->scanner;
    }

    /**
     * Get shipping.
     *
     * @return bool
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Get file.
     *
     * @return bool
     */
    public function getFile()
    {
        return $this->file;
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
     * Set tagSpec
     *
     * @param string $tagSpec
     *
     * @return ProjectType
     */
    public function settagSpec($tagSpec)
    {
        $this->tagSpec = $tagSpec;

        return $this;
    }

    /**
     * Get tagSpec
     *
     * @return string
     */
    public function gettagSpec()
    {
        return $this->tagSpec;
    }

    
   /**
     * Set addressProjectLabel
     *
     * @param string $addressProjectLabel
     *
     * @return ProjectType
     */
    public function setAddressProjectLabel($addressProjectLabel)
    {
        $this->addressProjectLabel = $addressProjectLabel;

        return $this;
    }

    /**
     * Get addressProjectLabel
     *
     * @return string
     */
    public function getAddressProjectLabel()
    {
        return $this->addressProjectLabel;
    }
  
     /**
     * Set addressProject
     *
     * @param boolean $addressProject
     *
     * @return ProjectType
     */
    public function setAddressProject($addressProject)
    {
        $this->addressProject = $addressProject;

        return $this;
    }

    /**
     * Get addressProject
     *
     * @return boolean
     */
    public function getAddressProject()
    {
        return ($this->addressProject==true);
    }
    /**
     * Get addressProject
     *
     * @return boolean
     */
    public function isAddressProject()
    {
        return ($this->addressProject==true);
    }
   /**
     * Set shippingChoice
     *
     * @param boolean $shippingChoice
     *
     * @return ProjectType
     */
    public function setShippingChoice($shippingChoice)
    {
        $this->shippingChoice = $shippingChoice;

        return $this;
    }

    /**
     * Get shippingChoice
     *
     * @return boolean
     */
    public function getShippingChoice()
    {
        return ($this->shippingChoice==true);
    }
    /**
     * Get shippingChoice
     *
     * @return boolean
     */
    public function isShippingChoice()
    {
        return ($this->shippingChoice==true);
    }
}
