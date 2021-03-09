<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectStatusUpdate;
use AppBundle\Entity\Quotation;
use AppBundle\Event\ProjectEvent;
use AppBundle\Event\ProjectEvents;
use AppBundle\Event\QuotationEvent;
use AppBundle\Service\ProjectManager;
use AppBundle\Service\QuotationManager;
use AppBundle\Service\SendinBlue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class ProjectListener implements EventSubscriberInterface
{
    private $router;

    private $entityManager;

    private $projectManager;

    private $sendinBlue;

    public function __construct(RouterInterface $router, ObjectManager $entityManager, ProjectManager $projectManager,QuotationManager $quotationManager, SendinBlue $sendinBlue)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->projectManager = $projectManager;
        $this->quotationManager = $quotationManager;
        $this->sendinBlue = $sendinBlue;
    }

    public static function getSubscribedEvents()
    {
        return array(
            ProjectEvents::PRE_PERSIST        => 'generateProjectReference',
            ProjectEvents::PRE_STATUS_UPDATE  => 'addProjectStatusUpdate',
            ProjectEvents::POST_STATUS_UPDATE => array( array('sendStatusUpdateNotifications', 10), array('manageQuotationProject', -11))
        );
    }

    /**
     * Generate an unique Project reference
     *
     * @param ProjectEvent $event
     */
    public function generateProjectReference(ProjectEvent $event)
    {
        $project = $event->getProject();
        if(!$project->getReference()) {
            $letters = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
            $numbers = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $reference = strtoupper($letters) . $numbers;
            $project->setReference($reference);
        }
    }

    /**
     * Add a project status update to the project
     *
     * @param ProjectEvent $event
     */
    public function addProjectStatusUpdate(ProjectEvent $event)
    {
        $project = $event->getProject();
        $statusUpdate = new ProjectStatusUpdate();
        $statusUpdate->setStatus($project->getStatus());
        $statusUpdate->setOrigin($event->getOrigin());
        $project->addStatusUpdate($statusUpdate);
    }

   /**
     * Manage Quotation & Project (if order status is STATUS_NEW & type = design)
     *
     * @param OrderEvent $event
     */
    public function manageQuotationProject(ProjectEvent $event)
    {
        $project = $event->getProject();

        if (Project::STATUS_CANCEL === $project->getStatus() ) {

            //Foreach Quotation update status : 1 STATUS_ACCEPTED / X STATUS_DISCARDED, only if current status is STATUS_DISPATCHED and STATUS_CLOSED for other
            $quotations = $project->getQuotations();
            foreach ($quotations as $quotation) {
                if (Quotation::STATUS_REFUSED != $quotation->getStatus()) {
                    /** @var Quotation $quotation */
                    $status = Quotation::STATUS_CLOSED;
                    if ((Quotation::STATUS_DISPATCHED === $quotation->getStatus()) or (Quotation::STATUS_SENT === $quotation->getStatus()) or (Quotation::STATUS_NOT_DISPATCHED === $quotation->getStatus())  ) {
                        $status = Quotation::STATUS_DISCARDED;
                    } 
                    $this->quotationManager->updateStatus($quotation, $status, QuotationEvent::ORIGIN_SYSTEM);
                }
            }

        }
    }





    /**
     * Send e-mail notifications upon project status update
     *
     * @param ProjectEvent $event
     */
    public function sendStatusUpdateNotifications(ProjectEvent $event)
    {
        $project = $event->getProject();
        switch ($project->getStatus()) {

            case Project::STATUS_SENT:
                // common email vars
                $emailVars = array(
                    'customerName' => $project->getCustomer()->getFullname(),
                    'NomProjet'    => $project->getName(),
                    'RefProjet'    => $project->getReference(),
                    'DateProjet'   => $project->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                    'DateCloture'  => $project->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i')
                );

                // send customer e-mail
                $emailVars['accountUrl'] = $this->router->generate('design_form', array('reference' => $project->getReference()), $this->router::ABSOLUTE_URL);
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_PROJECT_SENT_CUSTOMER,
                    $project->getCustomer()->getEmail(),
                    $project->getCustomer()->getFullname(),
                    $emailVars
                );

                // send admin e-mail
                $emailVars['accountUrl'] = $this->router->generate('admin_project_see', array('reference' => $project->getReference()), $this->router::ABSOLUTE_URL);
                $this->sendinBlue->sendTransactional(
                    SendinBlue::TEMPLATE_ID_PROJECT_SENT_ADMIN,
                    $this->sendinBlue->getAdminNotificationEmailAddress(),
                    $this->sendinBlue->getEmailFromName(),
                    $emailVars
                );

                break;

                case Project::STATUS_CREATED:
                    // common email vars
                    $emailVars = array(
                        'customerName' => $project->getCustomer()->getFullname(),
                        'NomProjet'    => $project->getName(),
                        'RefProjet'    => $project->getReference(),
                        'DateProjet'   => $project->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                        'DateCloture'  => $project->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                        'ReturnReason'  => $project->getReturnReason()
                    );
                    
                    if ($project->getReturnReason() != NULL ) {
                            // send customer e-mail
                            $emailVars['accountUrl'] = $this->router->generate('design_form', array('reference' => $project->getReference()), $this->router::ABSOLUTE_URL);
                            $this->sendinBlue->sendTransactional(
                                SendinBlue::TEMPLATE_ID_PROJECT_MODIFY_CUSTOMER,
                                $project->getCustomer()->getEmail(),
                                $project->getCustomer()->getFullname(),
                                $emailVars
                            );
                        }
    
                    break;
                    case Project::STATUS_DELETED:
                        // common email vars
                        $emailVars = array(
                            'customerName' => $project->getCustomer()->getFullname(),
                            'NomProjet'    => $project->getName(),
                            'RefProjet'    => $project->getReference(),
                            'DateProjet'   => $project->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                            'DateCloture'  => $project->getClosedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
                            'DelationReason'  => $project->getDeletionReason()
                        );
                        
                        if ($project->getDeletionReason() != NULL ) {
                                // send customer e-mail
                                $emailVars['accountUrl'] = $this->router->generate('design_form', array('reference' => $project->getReference()), $this->router::ABSOLUTE_URL);
                                $this->sendinBlue->sendTransactional(
                                    SendinBlue::TEMPLATE_ID_PROJECT_DELETE_CUSTOMER,
                                    $project->getCustomer()->getEmail(),
                                    $project->getCustomer()->getFullname(),
                                    $emailVars
                                );
                            }
        
                        break;



        }
    }
}