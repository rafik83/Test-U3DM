<?php

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\Api\SMTPApi;
use SendinBlue\Client\Model\AddContactToList;
use SendinBlue\Client\Model\CreateContact;
use SendinBlue\Client\Model\RemoveContactFromList;
use SendinBlue\Client\Model\SendSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmailSender;
use SendinBlue\Client\Model\SendSmtpEmailTo;

/**
 * @see https://github.com/sendinblue/APIv3-symfony-bundle
 * @see https://github.com/sendinblue/APIv3-php-library
 */
class SendinBlue
{
    /**
     * SendinBlue Template IDs
     */
    const TEMPLATE_ID_ENABLE_ACCOUNT = 7;
    const TEMPLATE_ID_RESET_PASSWORD = 16;
    const TEMPLATE_ID_PROSPECT = 18;
    const TEMPLATE_ID_ORDER_MESSAGE_NOTIFICATION = 15;
    const TEMPLATE_ID_ORDER_CONFIRMATION_CUSTOMER = 10;
    const TEMPLATE_ID_ORDER_CONFIRMATION_MAKER = 9;
    const TEMPLATE_ID_ORDER_CANCELLATION_CUSTOMER = 12;
    const TEMPLATE_ID_ORDER_CANCELLATION_MAKER = 14;
    const TEMPLATE_ID_ORDER_SHIPPED = 11;
    const TEMPLATE_ID_ORDER_REFUNDED = 13;
    const TEMPLATE_ID_ORDER_READY_FOR_PICKUP = 20;
    const TEMPLATE_ID_ORDER_AWAITING_SEPA = 21;
    const TEMPLATE_ID_ORDER_RATING = 23;
    const TEMPLATE_ID_ORDER_FILE_AVAILABLE_CUSTOMER = 47;
    const TEMPLATE_ID_ORDER_FILE_REJECTED_MAKER = 49;
    const TEMPLATE_ID_ORDER_FILE_VALIDATED_MAKER = 50;
    
    const TEMPLATE_ID_PROJECT_SENT_CUSTOMER = 35;
    const TEMPLATE_ID_PROJECT_SENT_ADMIN = 36;
    const TEMPLATE_ID_PROJECT_DISPATCHED_MAKER = 37;
    const TEMPLATE_ID_PROJECT_MODIFY_CUSTOMER = 86;
    const TEMPLATE_ID_PROJECT_DELETE_CUSTOMER = 87;
    const TEMPLATE_ID_QUOTATION_SENT_ADMIN = 38;
    const TEMPLATE_ID_QUOTATION_DISPATCHED_CUSTOMER = 39;
    const TEMPLATE_ID_QUOTATION_ACCEPTED_MAKER = 40;
    const TEMPLATE_ID_QUOTATION_DISCARDED_MAKER = 41;
    const TEMPLATE_ID_QUOTATION_SENT_TO_CORRECTION_MAKER = 42;
    const TEMPLATE_ID_QUOTATION_MESSAGE_NOTIFICATION = 46;
    const TEMPLATE_ID_MODEL_CORRECTION_MESSAGE_NOTIFICATION = 67;
    const TEMPLATE_ID_MODEL_DELETE_MESSAGE_NOTIFICATION = 68;
    const TEMPLATE_ID_MODEL_SIGNAL_MAKER_NOTIFICATION = 69;
    const TEMPLATE_ID_MODEL_SIGNAL_CUSTOMER_NOTIFICATION = 70;
    const TEMPLATE_ID_MODEL_COMMENT_VALID_NOTIFICATION = 72;
    const TEMPLATE_ID_PROJECT_DISPATCHED_MAKER_PROSPECT = 74;
    
    const TEMPLATE_ID_MESSAGE_MODERATE = 88;
    const TEMPLATE_ID_ORDER_RATING_F1 = 89;
    const TEMPLATE_ID_ORDER_RATING_F2 = 90;
    const TEMPLATE_ID_ORDER_RATING_F3 = 91;
    const TEMPLATE_ID_ORDER_RATE = 92;
    

    /**
     * SendinBlue List IDs
     */
    const LIST_ID_NEWSLETTER = 5;

    /**
     * @var SMTPApi
     */
    private $smtpClient;

    /**
     * @var ContactsApi
     */
    private $contactsClient;

    /**
     * @var string
     */
    private $emailFromAddress;

    /**
     * @var string
     */
    private $emailFromName;

    /**
     * @var string
     */
    private $adminNotificationEmailAddress;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * SendinBlue constructor
     *
     * @param SMTPApi $smtpClient
     * @param ContactsApi $contactsClient
     * @param string $emailFromAddress
     * @param string $emailFromName
     * @param string $adminNotificationEmailAddress
     * @param LoggerInterface $logger
     */
    public function __construct(SMTPApi $smtpClient, ContactsApi $contactsClient, $emailFromAddress, $emailFromName, $adminNotificationEmailAddress, LoggerInterface $logger)
    {
        $this->smtpClient = $smtpClient;
        $this->contactsClient = $contactsClient;
        $this->emailFromAddress = $emailFromAddress;
        $this->emailFromName = $emailFromName;
        $this->adminNotificationEmailAddress = $adminNotificationEmailAddress;
        $this->logger = $logger;
    }

    /**
     * @see https://developers.sendinblue.com/v3.0/reference#sendtransacemail
     *
     * @param int $templateId
     * @param string $toEmail
     * @param string $toName
     * @param array|null $templateParams
     */
    public function sendTransactional($templateId, $toEmail, $toName, $templateParams = null)
    {
        $sendSmtpEmail = new SendSmtpEmail();

        // sender
        $sender = new SendSmtpEmailSender();
        $sender->setName($this->emailFromName);
        $sender->setEmail($this->emailFromAddress);
        $sendSmtpEmail->setSender($sender);

        // receiver
        $receiver = new SendSmtpEmailTo();
        $receiver->setName($toName);
        $receiver->setEmail($toEmail);
        $sendSmtpEmail->setTo(array($receiver));

        // template
        $sendSmtpEmail->setTemplateId($templateId);
        $sendSmtpEmail->setParams($templateParams);

        try {
            $this->smtpClient->sendTransacEmail($sendSmtpEmail);
        } catch (\Exception $exception) {
            $this->logger->critical('SendinBlue Exception on sendTransactional with template ' . $templateId . ' to ' . $toEmail . ': ' . $exception->getMessage());
        }
    }

    /**
     * @see https://developers.sendinblue.com/v3.0/reference#addcontacttolist-1
     * @see https://developers.sendinblue.com/v3.0/reference#removecontactfromlist
     *
     * @param string $email
     * @param bool $subscribe
     */
    public function subscribeToNewsletter($email, $subscribe = true)
    {
        try {
            if ($subscribe) {
                try {
                    // check if contact exists (an exception will be thrown if not found)
                    $this->contactsClient->getContactInfo($email);
                } catch(\Exception $exception) {
                    // contact does not exist, create it
                    $contact = new CreateContact();
                    $contact->setEmail($email);
                    $this->contactsClient->createContact($contact);
                }
                // add the contact to the list (note: an exception will be thrown if contact already in list)
                $emailsList = new AddContactToList();
                $emailsList->setEmails(array($email));
                $this->contactsClient->addContactToList(self::LIST_ID_NEWSLETTER, $emailsList);
            } else {
                // unsubscribe
                $emailsList = new RemoveContactFromList();
                $emailsList->setEmails(array($email));
                $this->contactsClient->removeContactFromList(self::LIST_ID_NEWSLETTER, $emailsList);
            }
        } catch (\Exception $exception) {
            $this->logger->critical('SendinBlue Exception on subscribeToNewsletter for ' . $email . ': ' . $exception->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getEmailFromName()
    {
        return $this->emailFromName;
    }

    /**
     * @return string
     */
    public function getAdminNotificationEmailAddress()
    {
        return $this->adminNotificationEmailAddress;
    }
}