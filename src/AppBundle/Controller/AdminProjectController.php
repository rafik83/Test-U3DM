<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Entity\Quotation;
use AppBundle\Event\ProjectEvent;
use AppBundle\Form\ProjectDeletionType;
use AppBundle\Form\ProjectReturnType;
use AppBundle\Service\ProjectManager;
use AppBundle\Form\ProjectClosedAtType;
use AppBundle\Form\ProjectType;
use AppBundle\Form\SearchMakerProjectType;
use AppBundle\Event\QuotationEvent;
use AppBundle\Event\QuotationEvents;
use AppBundle\Service\QuotationManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/%app.admin_directory%/project")
 */
class AdminProjectController extends Controller
{
    /**
     * @Route("/list", name="admin_project_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listAction(ObjectManager $entityManager)
    {
        $projects = $entityManager->getRepository('AppBundle:Project')->findProjectsForAdmin();
        return $this->render('admin/project/list.html.twig', array('projects' => $projects));
    }

    /**
     * @Route("/{reference}", name="admin_project_see")
     *
     * @param Project $project
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param ProjectManager $projectManager
     * @return Response
     */
    public function seeAction(Request $request, ObjectManager $entityManager, Project $project, ProjectManager $projectManager)
    {


        if($project->getStatus() == Project::STATUS_SENT || $project->getStatus() == Project::STATUS_CREATED){

            $disabledField = false;

        } else {

            $disabledField = true;

        }

        $formSearch = $this->createForm(SearchMakerProjectType::class);
        $formClosedAt = $this->createForm(ProjectClosedAtType::class, $project);
        $formProject = $this->createForm(ProjectType::class, $project,['disabled'=> $disabledField]);

        // Update project
        $formProject->handleRequest($request);
        if ($formProject->isSubmitted() && $formProject->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.project.flash.update.project');
        }

        //Type project with scanner required
        $typeProjectWithScanner = $entityManager->getRepository('AppBundle:ProjectType')->findBy(array('scanner'=> true,'enabled' => true));

        //Automatic search maker
        $makers = [];
        $quotations = [];
        if(count($project->getQuotations()) < 1 && $project->getStatus() == Project::STATUS_SENT){

            $makers = $projectManager->searchMaker($project);

        } else {

            $quotations = $project->getQuotations();
        }

        //Result search
        $resultSearch = [];
        $keyword = null;
        $formSearch->handleRequest($request);
        if($formSearch->isSubmitted() && $formSearch->isValid()){

            $excludeMakers = [];

            //Maker Exclude If no quotation exist
            if(count($quotations) == 0){

                foreach ($makers as $maker ) {
                    $excludeMakers[] = $maker->getId();
                }

            } else {

                foreach ($project->getQuotations() as $quotation ) {
                    $excludeMakers[] = $quotation->getMaker()->getId();
                }

            } 

            $resultSearch = $entityManager->getRepository('AppBundle:Maker')->searchDesigner($formSearch['keyword']->getData(),$excludeMakers);
            $keyword = $formSearch['keyword']->getData();

        }

        // Update ClosedAt
        $formClosedAt->handleRequest($request);
        if ($formClosedAt->isSubmitted() && $formClosedAt->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.project.flash.update.closed_at');
        }

        // deletion form
        $deletionForm = $this->createForm(ProjectDeletionType::class, $project);
        $deletionForm->handleRequest($request);
        if ($deletionForm->isSubmitted() && $deletionForm->isValid()) {
            // update the project status
            $projectManager->updateStatus($project, Project::STATUS_DELETED, ProjectEvent::ORIGIN_ADMIN);

            $this->addFlash('success', 'admin.project.flash.delete.success');
        }

        // return form
        $returnForm = $this->createForm(ProjectReturnType::class, $project);
        $returnForm->handleRequest($request);
        if ($returnForm->isSubmitted() && $returnForm->isValid()) {
            // update the project status
            $projectManager->updateStatus($project, Project::STATUS_CREATED, ProjectEvent::ORIGIN_ADMIN);

            $this->addFlash('success', 'admin.project.flash.return.success');
        }

        return $this->render('admin/project/see.html.twig', array(
                'project' => $project,
                'deletionForm' => $deletionForm->createView(),
                'returnForm' => $returnForm->createView(),
                'formClosedAt' => $formClosedAt->createView(),
                'formProject' => $formProject->createView(),
                'formSearch' => $formSearch->createView(),
                'projetTypeRequiredScan' => $typeProjectWithScanner,
                'makers' => $makers,
                'quotations' => $quotations,
                'resultSearch' => $resultSearch,
                'keyword' => $keyword
            ));
    }

    /**
     * @Route("/{reference}/cancel", name="admin_project_cancel")
     *
     * @param Project $project
     * @param ProjectManager $projectManager
     * @return Response
     */
    public function adminProjectCancel ( Project $project, ProjectManager $projectManager)
    {

        // update the quotation status
        $projectManager->updateStatus($project, Project::STATUS_CANCEL, ProjectEvent::ORIGIN_ADMIN);

        $this->addFlash('success', 'admin.project.flash.cancel.project');
        
        // display
        return $this->redirectToRoute('admin_project_see', array('reference' => $project->getReference()));
    }





    /**
     * @Route("/{reference}/dispatch", name="admin_project_dispatch")
     *
     * @param Project $project
     * @param Request $request
     * @param ProjectManager $projectManager
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function dispatchAction(Request $request, Project $project, ProjectManager $projectManager,EventDispatcherInterface $eventDispatcher, ObjectManager $entityManager)
    {
        // counter of actual dispatchs
        $nbDispatchs = 0;

        //Quotation creation
        foreach ($request->request->all() as $makerId) {

            //Maker
            $maker = $entityManager->getRepository('AppBundle:Maker')->find($makerId);

            // look for a pre-existing quotation for that project and maker, and do not create a new one if any exists
            $nbQuotations = $entityManager->getRepository('AppBundle:Quotation')->countForProjectAndMaker($project, $maker);
            if (0 < $nbQuotations) {
                continue;
            }

            // else create a new quotation
            $quotation = new Quotation();
            $quotation->setMaker($maker);
            $quotation->setProject($project);
            $quotation->setStatus(Quotation::STATUS_PENDING);
            $quotation->setProductionTime(0);

            // dispatch an event
            $eventDispatcher->dispatch(QuotationEvents::PRE_PERSIST, new QuotationEvent($quotation));

            // persist and flush
            $entityManager->persist($quotation);
            $entityManager->flush();

            // dispatch an event
            $eventDispatcher->dispatch(QuotationEvents::POST_PERSIST, new QuotationEvent($quotation));

            // increment the counter of actual dispatchs
            $nbDispatchs++;
        }

        // update the project status if any dispatch occured
        if (0 < $nbDispatchs) {
            $projectManager->updateStatus($project, Project::STATUS_DISPATCHED, ProjectEvent::ORIGIN_ADMIN);
        }

        $this->addFlash('success', 'admin.project.flash.dispatch.success');

        // redirect to the order page
        return $this->redirectToRoute('admin_project_see', array('reference' => $project->getReference()));

    }

    /**
     * @Route("/{reference}/delete-quotation/{id}", name="admin_project_quotation_delete")
     *
     * @param Project $project
     * @param Quotation $quotation
     * @param Request $request
     * @param QuotationManager $quotationManager
     * @return Response
     * @ParamConverter("project", options={"mapping": {"reference": "reference"}}))
     * @ParamConverter("quotation", options={"mapping": {"id": "id"}})
     */
    public function deleteQuotationAction(Project $project, Quotation $quotation, Request $request, QuotationManager $quotationManager)
    {


        // update the quotation status
        $quotationManager->updateStatus($quotation, Quotation::STATUS_REFUSED, QuotationEvent::ORIGIN_ADMIN);

        $this->addFlash('success', 'admin.project.flash.delete.quotation.success');

        // redirect to the project page
        return $this->redirectToRoute('admin_project_see', array('reference' => $project->getReference()));

    }


}