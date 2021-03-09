<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Maker;
use AppBundle\Entity\Project;
use AppBundle\Event\ProjectEvent;
use AppBundle\Service\ProjectManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProjectController extends Controller
{
    /**
     * @Route("/%app.user_directory%/mon-compte-client/mes-projet", name="project_customer_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function customerListAction(ObjectManager $entityManager)
    {
        $projects = $entityManager->getRepository('AppBundle:Project')->findProjectsForCustomer($this->getUser());

        return $this->render('front/user/project/list.html.twig', array('projects' => $projects));
    }



    
}