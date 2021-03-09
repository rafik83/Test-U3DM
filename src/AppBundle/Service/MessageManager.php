<?php
 
namespace AppBundle\Service;

use AppBundle\Entity\Message;
use AppBundle\Entity\Order;
use AppBundle\Entity\Quotation;
use AppBundle\Entity\ModerationRule;
use AppBundle\Entity\Setting;
use AppBundle\Event\OrderEvent;
use AppBundle\Event\OrderEvents;
use AppBundle\Service\OrderManager;
use AppBundle\Service\SendinBlue;
use AppBundle\Service\Mailer;
use AppBundle\Repository\ModerationRuleRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Psr\Log\LoggerInterface;

class MessageManager
{
    private $entityManager;
    private $sendinBlue;
    private $eventDispatcher;
    private $mailer;
    private $orderManager;
    
    public function __construct( LoggerInterface $logger, RouterInterface $router , SendinBlue $sendinBlue, Mailer $mailer, ObjectManager $entityManager, OrderManager $orderManager)
    {
        $this->logger = $logger;
        $this->router = $router;
        $this->sendinBlue = $sendinBlue;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
    }

    /* Set moderation
    *
    * @return Message
    */
    public function setModerateText($message)
    {
        $ruleList = $this->entityManager->getRepository('AppBundle:ModerationRule')->findAll();
        $boolTextModerate=false;
       
        foreach ($ruleList as $rule) {
            $originaltext = $message->getText();
            $newText = preg_replace ($rule->getExpression(),$rule->getReplace(),$originaltext);
            if ($newText <> $originaltext) {
                $message->setText($newText);
                $boolTextModerate = true;
                if ($rule->getNeedModerate() == true ) {
                    $message->setNeedModerate(true);
                }
            }
         }
        if ($boolTextModerate == true) {
            // if Text is moderate, add to the message, that it was modify by the plateform.
            $marketplaceMess = $this->entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::MESSAGE_MODERATE_TEXT)->getValue();
            $messageText = $message->getText();
            $pos = strpos($messageText, $marketplaceMess);
            if ($pos == false){
                $message->setText($messageText.chr(13).chr(13).$marketplaceMess);
            }
        }
        return $message;
    }

    /* Set moderation
    *
    * @return Message
    */
    public function setNeedModerateText($message)
    {
        $ruleList = $this->entityManager->getRepository('AppBundle:ModerationRule')->findAll();
     
        $originaltext = $message->getText();
        foreach ($ruleList as $rule) { 
            $newText = preg_replace ($rule->getExpression(),$rule->getReplace(),$originaltext);
            if ($newText <> $originaltext) {
                $originaltext = $newText ;
                if ($rule->getNeedModerate() == true ) {
                    $message->setNeedModerate(true);
                }
            }
        }
        return $message;
    }

    /**
     * Send Notification to client / maker or Admin if Message need to be moderate
     *
     * @param Message $message
     */
    public function sendMessageNotification (Message $message, $moderateByAdmin = false)
    {

        // Set if the message need to be moderate
        $this->setNeedModerateText($message);
        if ($message->isNeedModerate() == false) {
            // apply moderation, because it's can apply the automatic moderation
            $this->setModerateText($message);
            }

        $order = $message->getOrder ();
        $quotation = $message->getQuotation ();
        if ($order != null ) {
            if ($message->isAuthorMaker() ) {
                $mailReceiverEmail = $order->getCustomer()->getEmail();
                $mailSenderEmail   = $order->getMaker()->getUser()->getEmail();
                $mailReceiverName  = $order->getCustomer()->getFullname();
                $mailSenderName    = $order->getMaker()->getFullname();
                $accountUrl        = $this->router->generate('order_customer_see', array('reference' => $order->getReference()), RouterInterface::ABSOLUTE_URL);
                $accountUrlAuthor   = $this->router->generate('order_maker_see', array('reference' => $order->getReference()), RouterInterface::ABSOLUTE_URL);

            } else {
                $mailReceiverEmail = $order->getMaker()->getUser()->getEmail();
                $mailSenderEmail   = $order->getCustomer()->getEmail();
                $mailReceiverName  = $order->getMaker()->getFullname();
                $mailSenderName    = $order->getCustomer()->getFullname();
                $accountUrl        = $this->router->generate('order_maker_see', array('reference' => $order->getReference()), RouterInterface::ABSOLUTE_URL);
                $accountUrlAuthor   = $this->router->generate('order_customer_see', array('reference' => $order->getReference()), RouterInterface::ABSOLUTE_URL);

            } 
        } else if ($quotation != null ) {
            if ($message->isAuthorMaker() ) {
                $mailReceiverEmail = $quotation->getProject()->getCustomer()->getEmail();
                $mailSenderEmail   = $quotation->getMaker()->getUser()->getEmail();
                $mailReceiverName  = $quotation->getProject()->getCustomer()->getFullname();
                $mailSenderName    = $quotation->getMaker()->getFullname();
                $accountUrl        = $this->router->generate('quotation_customer_see', array('reference' => $quotation->getReference()), RouterInterface::ABSOLUTE_URL);
                $accountUrlAuthor  = $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), RouterInterface::ABSOLUTE_URL);

            } else {
                $mailReceiverEmail = $quotation->getMaker()->getUser()->getEmail();
                $mailSenderEmail   = $quotation->getProject()->getCustomer()->getEmail();
                $mailReceiverName  = $quotation->getMaker()->getFullname();
                $mailSenderName    = $quotation->getProject()->getCustomer()->getFullname();
                $accountUrl        = $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), RouterInterface::ABSOLUTE_URL);
                $accountUrlAuthor  = $this->router->generate('quotation_customer_see', array('reference' => $quotation->getReference()), RouterInterface::ABSOLUTE_URL);
            } 
        }

        if ($message->isNeedModerate () == False ) {
            // send a notification e-mail to the other party involved -- Only if the message no need to be moderate

            $attachmentText = '';
            if (null !== $message->getAttachmentName()) {
                $attachmentText = 'Le message contient une pièce jointe, que vous pouvez télécharger sur votre espace personnel.';
            }

            if ($order != null ) {
                $messageRef = "la commande " . $order->getReference();
                if ($order->getStatus() == Order::STATUS_FILE_MODERATE_REJECTED) {
                    $this->orderManager->updateStatus($order, Order::STATUS_FILE_REJECTED, OrderEvent::ORIGIN_CUSTOMER);
                } else {
                    $emailVars = array(
                        'receiverName'   => $mailReceiverName,
                        'senderName'     => $mailSenderName,
                        'orderReference' => $order->getReference(),
                        'messageText'    => $message->getText(),
                        'accountUrl'     => $accountUrl,
                        'attachment'     => $attachmentText,
                    );
                    $this->sendinBlue->sendTransactional(
                        SendinBlue::TEMPLATE_ID_ORDER_MESSAGE_NOTIFICATION,
                        $mailReceiverEmail,
                        $mailReceiverName,
                        $emailVars
                    );
                }



            } else if ($quotation != null ) {
                $messageRef = "le devis " . $quotation->getReference();
                $emailVars = array(
                    'receiverName'    => $mailReceiverName,
                    'senderName'      => $mailSenderName,
                    'projetReference' => $quotation->getProject()->getReference(),
                    'devisReference'  => $quotation->getReference(),
                    'messageText'     => $message->getText(),
                    'accountUrl'      => $accountUrl,
                    'attachment'      => $attachmentText,
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_QUOTATION_MESSAGE_NOTIFICATION,
                    $mailReceiverEmail,
                    $mailReceiverName,
                    $emailVars
                );
            }

            if ($moderateByAdmin == true){
                // inform the author that his message has been moderate by admin



                $emailVars = array(
                    'receiverName'   => $mailReceiverName,
                    'senderName'     => $mailSenderName,
                    'messageRef'    => $messageRef,
                    'messageText'    => $message->getText(),
                    'accountUrl'     => $accountUrlAuthor
                );
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_MESSAGE_MODERATE,
                    $mailSenderEmail,
                    $mailSenderName,
                    $emailVars
                );
            }

        } else {
            if (($moderateByAdmin == null) or ($moderateByAdmin == false)) {
                // Message need to be moderate, send notification to admin.
                $subject = "Message à modérer";
                
                $body  = '<b>De </b> : ' . $mailSenderName ;
                $body .= '<br>';
                $body .= '<b>a </b> : ' . $mailReceiverName;
                $body .= '<br>';
                $body .= '<b>Texte</b> : ' . $message->getText();
                $body .= '<br>';
                $accountUrl = $this->router->generate('admin_message_edit', array('id' => $message->getId()), RouterInterface::ABSOLUTE_URL);

                $body .= '<br> Acces pour moderation ' . $accountUrl;

                // send the notification message to the configured admin address
                $notif = $this->mailer->createAdminNotificationMessage($subject, $body);
                $this->mailer->send($notif);
            }
        }


    }
}

