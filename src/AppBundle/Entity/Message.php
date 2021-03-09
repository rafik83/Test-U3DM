<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
 * @Vich\Uploadable
 */
class Message
{
    /**
     * Add createdAt and updatedAt fields
     */
    use TimestampableEntity;
    
    /**
     * @var MessageManager
     */
    private $messageManager;
   
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="messages")
     */
    private $order;

    /**
     * @var Quotation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quotation", inversedBy="messages")
     */
    private $quotation;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var bool
     *
     * @ORM\Column(name="author_maker", type="boolean")
     */
    private $authorMaker;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var bool
     *
     * @ORM\Column(name="need_moderate", type="boolean", options={"default" : 0})
     */
    private $needModerate = 0;



    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @var File
     *
     * @Vich\UploadableField(mapping="message_attachment", fileNameProperty="attachmentName", originalName="attachmentOriginalName")
     */
    private $attachmentFile;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_name", type="string", length=255, nullable=true)
     */
    private $attachmentName;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_name_original", type="string", length=255, nullable=true)
     */
    private $attachmentOriginalName;

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
     * @param Order|null $order
     *
     * @return Message
     */
    public function setOrder(Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set quotation
     *
     * @param Quotation|null $quotation
     *
     * @return Message
     */
    public function setQuotation(Quotation $quotation = null)
    {
        $this->quotation = $quotation;

        return $this;
    }

    /**
     * Get quotation
     *
     * @return Quotation|null
     */
    public function getQuotation()
    {
        return $this->quotation;
    }

    /**
     * Set author
     *
     * @param User $user
     *
     * @return Message
     */
    public function setAuthor(User $user)
    {
        $this->author = $user;

        return $this;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }
    
    /**
     * Set author maker
     *
     * @param bool $authorMaker
     *
     * @return Message
     */
    public function setAuthorMaker($authorMaker)
    {
        $this->authorMaker = $authorMaker;

        return $this;
    }

    /**
     * Is author maker
     *
     * @return bool
     */
    public function isAuthorMaker()
    {
        return $this->authorMaker;
    }

   
    /**
     * Set Message to Moderate
     *
     * @param bool $needModerate
     *
     * @return Message
     */
    public function setNeedModerate($needModerate)
    {
        $this->needModerate = $needModerate;

        return $this;
    }

    /**
     * Is message need to be moderate
     *
     * @return bool
     */
    public function isNeedModerate()
    {
        return $this->needModerate;
    }


    /**
     * Set text
     *
     * @param string $text
     *
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    
   



    /**
     * Set attachment file
     *
     * @see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md
     *
     * @param File|UploadedFile|null $file
     *
     * @return Message
     */
    public function setAttachmentFile($file = null)
    {
        $this->attachmentFile = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get attachment file
     *
     * @return File|null
     */
    public function getAttachmentFile()
    {
        return $this->attachmentFile;
    }

    /**
     * Set attachment name
     *
     * @param string|null $name
     *
     * @return Message
     */
    public function setAttachmentName($name = null)
    {
        $this->attachmentName = $name;

        return $this;
    }

    /**
     * Get attachment name
     *
     * @return string|null
     */
    public function getAttachmentName()
    {
        return $this->attachmentName;
    }

    /**
     * Set attachment original name
     *
     * @param string|null $name
     *
     * @return Message
     */
    public function setAttachmentOriginalName($name = null)
    {
        $this->attachmentOriginalName = $name;

        return $this;
    }

    /**
     * Get attachment original name
     *
     * @return string|null
     */
    public function getAttachmentOriginalName()
    {
        return $this->attachmentOriginalName;
    }

    /**
     * Get authorMaker.
     *
     * @return bool
     */
    public function getAuthorMaker()
    {
        return $this->authorMaker;
    }
}
