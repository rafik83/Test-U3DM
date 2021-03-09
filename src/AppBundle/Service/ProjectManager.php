<?php
 
namespace AppBundle\Service;

use AppBundle\Entity\Project;
use AppBundle\Event\ProjectEvent;
use AppBundle\Event\ProjectEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectManager
{
    private $entityManager;

    private $eventDispatcher;

    private $router;


    /**
     * ProjectManager constructor
     *
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher,UrlGeneratorInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;

    }

    /**
     * Update the project status
     *
     * @param Project $project
     * @param int $newStatus
     * @param string $origin
     */
    public function updateStatus(Project $project, $newStatus, $origin)
    {
        // update project status
        $project->setStatus($newStatus);

        // dispatch an event
        $this->eventDispatcher->dispatch(ProjectEvents::PRE_STATUS_UPDATE, new ProjectEvent($project, $origin));

        // flush
        $this->entityManager->flush();

        // dispatch an event
        $this->eventDispatcher->dispatch(ProjectEvents::POST_STATUS_UPDATE, new ProjectEvent($project, $origin));
    }

    /**
     * Search Maker for project
     *
     * @param Project $project
     */
    public function searchMaker(Project $project)
    {

        $makers = $this->entityManager->getRepository('AppBundle:Maker')->findMakersForProject($project);

        $makersSelected = [];

        foreach ($makers as $maker) {
            
            // Check for skill : minimum 1 skill required if project have skills
            if(count($project->getSkills()) > 0){
                $skills = 0;
                foreach ($maker->getDesignSkills() as $makerSkill) {
                    foreach ($project->getSkills() as $skill) {

                        if($makerSkill->getId() == $skill->getId()){
                            $skills++;
                        }
                    }
                }
                if($skills == 0)
                    continue;
            }

            // Check for software : minimu 1 softare required if project have softwares
            if(count($project->getSoftwares()) > 0){
                $softwares = 0;
                foreach ($maker->getDesignSoftwares() as $makerSoftware) {
                    foreach ($project->getSoftwares() as $software) {

                        if($makerSoftware->getId() == $software->getId()){
                            $softwares++;
                        }
                    }
                }
                if($softwares == 0)
                    continue;
            }

            // Check for scanner
            if($project->getType()->isScanner()){

                $scanners = 0;
                if(count($maker->getScanners()) == 0)
                    continue;

                foreach ($maker->getScanners() as $scanner) {
                    
                    if(!$scanner->isVisible())
                        continue;

                    if (!$project->getDimensions()->fitsInto($scanner->getMaxDimensions()))
                        continue;
            
                    if (!$project->getDimensions()->fitsIntoMin($scanner->getMinDimensions()))
                        continue;

                    $scanners ++;

                }

                if($scanners == 0)
                    continue;


            }

            $makersSelected[] = $maker;

        }

        return $makersSelected;

    }


    /**
     * Encode project in json
     *
     * @param Project $project
     * @param UrlGeneratorInterface $generator
     */
    public function projectToJson(Project $project){

        //Skills
        $arraySkills = [];
        foreach ($project->getSkills() as $key) {
            $arraySkills[] = array('name' => $key->getName(), 'id' => $key->getId());
        }

        //Softwares
        $arraySoftwares = [];
        foreach ($project->getSoftwares() as $key) {
            $arraySoftwares[] = array('name' => $key->getName(), 'id' => $key->getId());
        }

        //Fields
        $arrayFields = [];
        foreach ($project->getFields() as $key) {
            $arrayFields[] = array('name' => $key->getName(), 'id' => $key->getId());
        }

        //Type
        $arrayType = [];
        $arrayType[] = array(
            'description' => $project->getType()->getDescription(),
            'id' => $project->getType()->getId(), 
            'name' => $project->getType()->getName(),
            'scanner' => $project->getType()->isScanner(),
            'tagSpec' => $project->getType()->gettagSpec(),
            'addressProject' => $project->getType()->getAddressProject(),
            'addressProjectLabel' => $project->getType()->getAddressProjectLabel(),
            'shippingChoice' => $project->getType()->getShippingChoice(),
            'shipping' => $project->getType()->getShipping()
        );

        //Files
        $arrayFiles = [];
        foreach ($project->getFiles() as $key) {
            $arrayFiles[] = array(
                'name' => $key->getName(), 
                'id' => $key->getId(), 
                'original_name' => $key->getOriginalName(),
                'url_download' => $this->router->generate('project_file_download',array('name' => $key->getName()),UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        //Dimensions
        $dim = $project->getDimensions();
        $arrayDimensions = array('x' => $dim->getX(),'y' => $dim->getY(),'z' => $dim->getZ());

        //Address
        if($project->getScanAddress()){
            $address = $project->getScanAddress();
            $arrayScanAddress = array(
                'street1' => $address->getStreet1(),
                'street2' => $address->getStreet2(),
                'zipcode' => $address->getZipcode(),
                'city' => $address->getCity(),
                'country' => $address->getCountry(),
                'telephone' => $address->gettelephone(),
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'company' => $address->getCompany(),
            );
        } else {
            $arrayScanAddress = null;
        }

        $orderUrl = null;
        $quotationUrl = null;
        if (null !== $project->getOrder()) {
            $orderUrl = $this->router->generate('order_customer_see', array('reference' => $project->getOrder()->getReference()), $this->router::ABSOLUTE_URL);
            if (null !== $project->getOrder()->getQuotation()) {
                $quotationUrl = $this->router->generate('quotation_customer_see', array('reference' => $project->getOrder()->getQuotation()->getReference()), $this->router::ABSOLUTE_URL);
            }
        }

        $jsonProject = json_encode(
            array(
                'id' => $project->getId(),
                'reference' => $project->getReference(),
                'name' => $project->getName(),
                'status' => $project->getStatus(),
                'description' => $project->getDescription(),
                'deletion_reason' => null !== $project->getDeletionReason() ? $project->getDeletionReason() : '',
                'return_reason' => $project->getReturnReason(),
                'skills' => $arraySkills,
                'softwares' => $arraySoftwares,
                'fields' => $arrayFields,
                'type' => $arrayType,
                'shipping_required' => ($project->getType()->isShippingChoice() == true),
                'files' => $arrayFiles,
                'delivery_time' => $project->getDeliveryTime(),
                'scan_on_site' => $project->getScanOnSite(),
                'scan_address' => $arrayScanAddress,
                'dimensions' => $arrayDimensions,
                'order_url' => $orderUrl,
                'quotation_url' => $quotationUrl
            )
        );

        return $jsonProject;

    }

    /**
     * Encode files in json
     *
     * @param Project $project
     * @param UrlGeneratorInterface $generator
     */
    public function filesToJson(Project $project){

        //Files
        $arrayFiles = [];
        foreach ($project->getFiles() as $key) {
            $arrayFiles[] = array(
                'name' => $key->getName(), 
                'id' => $key->getId(), 
                'original_name' => $key->getOriginalName(),
                'url_download' => $this->router->generate('project_file_download',array('name' => $key->getName()),UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        $json = json_encode(
            array(
                'files' => $arrayFiles,
            )
        );

        return $json;

    }

}