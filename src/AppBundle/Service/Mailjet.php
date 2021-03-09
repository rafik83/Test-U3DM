<?php

namespace AppBundle\Service;

use Mailjet\MailjetBundle\Client\MailjetClient;
use Mailjet\Resources;
use Psr\Log\LoggerInterface;

class Mailjet
{
    /**
     * Mailjet Template IDs
     */
    const TEMPLATE_ID_PROSPECT = 316279;
    const TEMPLATE_ID_RESET_PASSWORD = 375163;
    const TEMPLATE_ID_ORDER_CONFIRMATION_CUSTOMER = 375036;
    const TEMPLATE_ID_ORDER_CONFIRMATION_MAKER = 375066;
    const TEMPLATE_ID_ORDER_CANCELLATION_CUSTOMER = 375157;
    const TEMPLATE_ID_ORDER_CANCELLATION_MAKER = 375158;
    const TEMPLATE_ID_ORDER_SHIPPED = 375161;
    const TEMPLATE_ID_ORDER_REFUNDED = 375159;
    const TEMPLATE_ID_ENABLE_ACCOUNT = 399949;
    const TEMPLATE_ID_ORDER_MESSAGE_NOTIFICATION = 375166;

    /**
     * Mailjet List IDs
     */
    const LIST_ID_NEWSLETTER = 1791959;

    /**
     * @var MailjetClient
     */
    private $client;

    /**
     * @var string
     */
    private $emailFromAddress;

    /**
     * @var string
     */
    private $emailFromName;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Mailer constructor
     *
     * @param MailjetClient $client
     * @param string $emailFromAddress
     * @param string $emailFromName
     * @param LoggerInterface $logger
     */
    public function __construct(MailjetClient $client, $emailFromAddress, $emailFromName, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->emailFromAddress = $emailFromAddress;
        $this->emailFromName = $emailFromName;
        $this->logger = $logger;
    }

    /**
     * Send the reset password e-mail
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $resetUrl
     */
    public function sendResetPasswordEmail($toEmail, $toName, $resetUrl)
    {
        $toVars = array('name' => $toName, 'reset_password_url' => $resetUrl);
        $this->sendTransactional(self::TEMPLATE_ID_RESET_PASSWORD, $toEmail, $toName, $toVars);
    }

    /**
     * @see https://dev.mailjet.com/guides/#using-a-template-v3
     *
     * @param int $templateId
     * @param string $toEmail
     * @param string $toName
     * @param array|null $toVars
     */
    public function sendTransactional($templateId, $toEmail, $toName, $toVars = null)
    {

        $body = array(
            'FromEmail'     => $this->emailFromAddress,
            'FromName'      => $this->emailFromName,
            'MJ-TemplateID' => $templateId,
            'Recipients'    => array(
                array(
                    'Email' => $toEmail,
                    'Name'  => $toName,
                    'Vars'  => $toVars
                )
            ),
            'MJ-TemplateLanguage' => true,
        );
        try {
            $this->client->post(
                Resources::$Email,
                array(
                    'body' => $body
                )
            );
        } catch (\Exception $exception) {
            $this->logger->critical('Mailjet Exception on sendTransactional with template ' . $templateId . ' to ' . $toEmail);
        }
    }

    /**
     * @see https://dev.mailjet.com/email-api/v3/contactslist-managecontact/
     *
     * @param string $email
     * @param bool $subscribe
     */
    public function subscribeToNewsletter($email, $subscribe = true)
    {
        $action = 'addforce';
        if (false === $subscribe) {
            $action = 'remove';
        }
        $body = array(
            'Email'  => $email,
            'Action' => $action
        );
        try {
            $this->client->post(
                Resources::$ContactslistManagecontact,
                array(
                    'id' => self::LIST_ID_NEWSLETTER,
                    'body' => $body
                )
            );
        } catch (\Exception $exception) {
            $this->logger->critical('Mailjet Exception on subscribeToNewsletter for ' . $email);
        }
    }
}