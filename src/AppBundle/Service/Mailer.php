<?php

namespace AppBundle\Service;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $templating;

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
     * Mailer constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $templating
     * @param string $emailFromAddress
     * @param string $emailFromName
     * @param string $adminNotificationEmailAddress
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating, $emailFromAddress, $emailFromName, $adminNotificationEmailAddress)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->emailFromAddress = $emailFromAddress;
        $this->emailFromName = $emailFromName;
        $this->adminNotificationEmailAddress = $adminNotificationEmailAddress;
    }

    /**
     * Create a message to be sent as an admin notification.
     *
     * @param string $subject
     * @param string $body
     * @return \Swift_Message
     */
    public function createAdminNotificationMessage($subject, $body)
    {
        $message = \Swift_Message::newInstance()
            ->setTo($this->adminNotificationEmailAddress)
            ->setSubject($subject)
            ->setBody(
                $this->templating->render(
                    'email/notification.html.twig',
                    array('body' => $body)
                ),
                'text/html'
            );
        return $message;
    }

    /**
     * Create a message to be sent as an activation e-mail to a new user.
     * This is a temporary method before transactional templating is done.
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $enableUrl
     * @return \Swift_Message
     */
    public function createEnableMessage($toEmail, $toName, $enableUrl)
    {
        $message = \Swift_Message::newInstance()
            ->setTo(array($toEmail => $toName))
            ->setSubject('Activez votre compte')
            ->setBody(
                '<div>Activez votre compte en cliquant sur ce lien : <a href="'.$enableUrl.'">'.$enableUrl.'</a></div>',
                'text/html'
            );
        return $message;
    }

    /**
     * Set the message sender name and e-mail address, and sends it.
     *
     * @param \Swift_Message $message
     */
    public function send(\Swift_Message $message)
    {
        $message->setFrom($this->emailFromAddress, $this->emailFromName);
        $this->mailer->send($message);
    }
}