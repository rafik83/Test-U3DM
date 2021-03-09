<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Quotation;
use AppBundle\Entity\QuotationStatusUpdate;
use AppBundle\Event\QuotationEvent;
use AppBundle\Event\QuotationEvents;
use AppBundle\Service\QuotationManager;
use AppBundle\Service\SendinBlue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

use Psr\Log\LoggerInterface;

class QuotationListener implements EventSubscriberInterface
{
    private $router;

    private $entityManager;

    private $quotationManager;

    private $sendinBlue;

    private $logger;

    public function __construct(RouterInterface $router, ObjectManager $entityManager, QuotationManager $quotationManager, SendinBlue $sendinBlue, LoggerInterface $logger)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->quotationManager = $quotationManager;
        $this->sendinBlue = $sendinBlue;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            QuotationEvents::PRE_PERSIST        => 'generateQuotationReference',
            QuotationEvents::POST_PERSIST       => 'sendMakerNotification',
            QuotationEvents::PRE_STATUS_UPDATE  => 'addQuotationStatusUpdate',
            QuotationEvents::POST_STATUS_UPDATE => 'sendStatusUpdateNotifications',
            QuotationEvents::POST_ADMIN_SENT_TO_CORRECTION => 'sendCorrectionNotification'
        );
    }

    /**
     * Generate an unique Quotation reference
     *
     * @param QuotationEvent $event
     */
    public function generateQuotationReference(QuotationEvent $event)
    {
        $quotation = $event->getQuotation();
        $letters = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $numbers = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $reference = strtoupper($letters) . $numbers;
        $quotation->setReference($reference);
    }

    /**
     * Add an quotation status update to the order
     *
     * @param QuotationEvent $event
     */
    public function addQuotationStatusUpdate(QuotationEvent $event)
    {
        $quotation = $event->getQuotation();
        $statusUpdate = new QuotationStatusUpdate();
        $statusUpdate->setStatus($quotation->getStatus());
        $statusUpdate->setOrigin($event->getOrigin());
        $quotation->addStatusUpdate($statusUpdate);
    }

    /**
     * Send e-mail notifications upon quotation status update
     *
     * @param QuotationEvent $event
     */
    public function sendStatusUpdateNotifications(QuotationEvent $event)
    {

        $quotation = $event->getQuotation();
        switch ($quotation->getStatus()) {

            case Quotation::STATUS_SENT:
                // email vars
                $emailVars = array(
                    'RefProjet'    => $quotation->getProject()->getReference(),
                    'RefDevis'     => $quotation->getReference(),
                    'MakerName'    => $quotation->getMaker()->getFullname(),
                    'NomProjet'    => $quotation->getProject()->getName(),
                    'customerName' => $quotation->getProject()->getCustomer()->getFullname(),
                    'DateCloture'  => $quotation->getProject()->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'accountUrl'   => $this->router->generate('admin_quotation_see', array('reference' => $quotation->getReference()), $this->router::ABSOLUTE_URL)
                );

                // send admin e-mail
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_QUOTATION_SENT_ADMIN,
                    $this->sendinBlue->getAdminNotificationEmailAddress(),
                    $this->sendinBlue->getEmailFromName(),
                    $emailVars
                );

                break;

                case Quotation::STATUS_DISPATCHED:
                // email vars
                $emailVars = array(
                    'RefProjet'    => $quotation->getProject()->getReference(),
                    'customerName' => $quotation->getProject()->getCustomer()->getFullname(),
                    'MakerSociete' => $quotation->getMaker()->getCompany(),
                    'NomProjet'    => $quotation->getProject()->getName(),
                    'DateProjet'   => $quotation->getProject()->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'DateCloture'  => $quotation->getProject()->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'accountUrl'   => $this->router->generate('design_form', array('reference' => $quotation->getProject()->getReference()), $this->router::ABSOLUTE_URL)
                );

                // send admin e-mail
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_QUOTATION_DISPATCHED_CUSTOMER,
                    $quotation->getProject()->getCustomer()->getEmail(),
                    $quotation->getProject()->getCustomer()->getFullname(),
                    $emailVars
                );

                break;

            case Quotation::STATUS_ACCEPTED:
                // email vars
                $emailVars = array(
                    'RefDevis'          => $quotation->getReference(),
                    'MakerName'         => $quotation->getMaker()->getFullname(),
                    'NomProjet'         => $quotation->getProject()->getName(),
                    'TypeProjet'        => $quotation->getProject()->getType()->getName(),
                    'ListeApplications' => $quotation->getProject()->getReadableFields(),
                    'DateCloture'       => $quotation->getProject()->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'accountUrl'        => $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), $this->router::ABSOLUTE_URL)
                );

                // send admin e-mail
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_QUOTATION_ACCEPTED_MAKER,
                    $quotation->getMaker()->getUser()->getEmail(),
                    $quotation->getMaker()->getFullname(),
                    $emailVars
                );

                break;

            case Quotation::STATUS_DISCARDED:
                // email vars
                $emailVars = array(
                    'RefDevis'          => $quotation->getReference(),
                    'MakerName'         => $quotation->getMaker()->getFullname(),
                    'NomProjet'         => $quotation->getProject()->getName(),
                    'TypeProjet'        => $quotation->getProject()->getType()->getName(),
                    'ListeApplications' => $quotation->getProject()->getReadableFields(),
                    'DateCloture'       => $quotation->getProject()->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'accountUrl'        => $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), $this->router::ABSOLUTE_URL)
                );
                
                // Send maker-email only if project is not too old (less than 2 month after project's date closure)
                //$datelimitSendMail =$quotation->getProject()->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))   ;

                $datelimitSendMail = new \DateTime($quotation->getProject()->getClosedAt()->format('Y-m-d'), new \DateTimeZone('UTC') )   ;
                $datelimitSendMail->add(new \DateInterval('P61D'));
                $today = new \DateTime('now noon', new \DateTimeZone('UTC'));
            
                
                //$datelimitSendMail->add(new DateInterval('P2M'));
             
                $this->logger->info(sprintf('Prepare Mail pour Maker %s clos le %s limite %s  ',$quotation->getMaker()->getFullname(),  $datelimitSendMail->format('d/m/Y à H:i'), $today->format('d/m/Y à H:i')) );

                if ($datelimitSendMail >= $today ) {
                    // send maker e-mail
                    $this->logger->info(sprintf('Envoi du Mail a %s ',$quotation->getMaker()->getFullname()));
                    $this->sendinBlue->sendTransactional(
                        SendinBlue::TEMPLATE_ID_QUOTATION_DISCARDED_MAKER,
                        $quotation->getMaker()->getUser()->getEmail(),
                        $quotation->getMaker()->getFullname(),
                        $emailVars
                    );
                }
                break;
        }
    }



    /**
     * Send maker e-mail notification when admin sends quotation to correction
     *
     * @param QuotationEvent $event
     */
    public function sendCorrectionNotification(QuotationEvent $event)
    {
        $quotation = $event->getQuotation();

        // email vars
        $emailVars = array(
            'RefDevis'          => $quotation->getReference(),
            'MakerName'         => $quotation->getMaker()->getFullname(),
            'TypeProjet'        => $quotation->getProject()->getType()->getName(),
            'NomProjet'         => $quotation->getProject()->getName(),
            'DescModerateur'    => $quotation->getCorrectionReason(),
            'ListeApplications' => $quotation->getProject()->getReadableFields(),
            'accountUrl'        => $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), $this->router::ABSOLUTE_URL)
        );

        // send maker e-mail
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_QUOTATION_SENT_TO_CORRECTION_MAKER,
            $quotation->getMaker()->getUser()->getEmail(),
            $quotation->getMaker()->getFullname(),
            $emailVars
        );
    }

    /**
     * Send maker e-mail notification when a new quotation is created
     *
     * @param QuotationEvent $event
     */
    public function sendMakerNotification(QuotationEvent $event)
    {
        $quotation = $event->getQuotation();

        $project = $quotation->getProject();

             
        if ($quotation->getMaker()->getUser()->isEnabled() == 1) {
              // email vars
        $emailVars = array(
            'MakerName'         => $quotation->getMaker()->getFullname(),
            'NomProjet'         => $project->getName(),
            'TypeProjet'        => $project->getType()->getName(),
            'ListeApplications' => $project->getReadableFields(),
            'RefDevis'          => $quotation->getReference(),
            'DateCloture'       => $project->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
            'accountUrl'        => $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), $this->router::ABSOLUTE_URL),
            'EmailMaker'         => $quotation->getMaker()->getUser()->getEmail()
            );
            // send maker e-mail (classic e-mail)
            $this->sendinBlue->sendTransactional(
                SendinBlue::TEMPLATE_ID_PROJECT_DISPATCHED_MAKER,
                $quotation->getMaker()->getUser()->getEmail(),
                $quotation->getMaker()->getFullname(),
                $emailVars
            );
        }else {
              // email vars
        $emailVars = array(
            'MakerName'         => $quotation->getMaker()->getFullname(),
            'NomProjet'         => $project->getName(),
            'TypeProjet'        => $project->getType()->getName(),
            'ListeApplications' => $project->getReadableFields(),
            'RefDevis'          => $quotation->getReference(),
            'DateCloture'       => $project->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
            'accountUrl'        => $this->router->generate('quotation_maker_see', array('reference' => $quotation->getReference()), $this->router::ABSOLUTE_URL),
            'ActivateUrl'        => $this->router->generate('user_register_enable', array('token' => $quotation->getMaker()->getUser()->getEnableToken()), $this->router::ABSOLUTE_URL),
            'EmailMaker'         => $quotation->getMaker()->getUser()->getEmail()
            );
        // send maker a special e-mail because this maker must activate his account before
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_PROJECT_DISPATCHED_MAKER_PROSPECT,
            $quotation->getMaker()->getUser()->getEmail(),
            $quotation->getMaker()->getFullname(),
            $emailVars
        );

        }

    }

}