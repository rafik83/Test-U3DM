<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderItemPrint;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectFile;
use AppBundle\Entity\User;
use AppBundle\Event\OrderEvent;
use AppBundle\Service\ProjectManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ProjectFileController extends Controller
{
    /**
     * @Route("/project-file/{name}/download", name="project_file_download")
     *
     * @param   ProjectFile $file
     * @param   ObjectManager $entityManager
     * @param   ProjectManager $projectManager
     * @return  BinaryFileResponse
     */
    public function downloadAction(ProjectFile $file, ObjectManager $entityManager, ProjectManager $projectManager)
    {

        //Add secu with sessions and owner

        // get file from project directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/project/' . $file->getName());

        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getOriginalName());
        return $response;
    }

    /**
     * @Route("/project-file/{idFile}/{idProject}/delete", name="project_file_delete")
     *
     * @param   ObjectManager $entityManager
     * @return  JsonResponse
     */
    public function deleteFile(ObjectManager $entityManager, $idFile = null, $idProject = null, Filesystem $fileSystem)
    {

        //Add secu with sessions and owner
        $project = $entityManager->getRepository('AppBundle:Project')->findOneBy(array('id' => $idProject ));
        $fileRequest = $entityManager->getRepository('AppBundle:ProjectFile')->findOneBy(array('id' => $idFile, 'project' => $project ));

        if($fileRequest){

            $fileSystem->remove($this->get('kernel')->getProjectDir() . '/var/uploads/project/' . $fileRequest->getName());

            $project->removeFile($fileRequest);
            // persist
            $entityManager->persist($project);
            $entityManager->flush();

            return new JsonResponse(json_encode(array('message' => 'File successfully deleted'), JSON_FORCE_OBJECT), Response::HTTP_OK);


        } else {

            return new JsonResponse(json_encode(array('message' => 'Error delete file'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }


}