<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Maker;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\DesignEngine;

/**
 * @Route("/api/design")
 */
class ApiDesignController extends Controller
{
    /**
     * @Route("/referentiel", name="api_design_referentiel_ajax")
     *
     * @param Request       $request
     * @param ObjectManager $entityManager
     * @param DesignEngine $engine
     * @return JsonResponse
     */
    public function designReferentielAjaxAction(Request $request, ObjectManager $entityManager,DesignEngine $engine)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        $result = $engine->getRef();

        // return JSON
        return new JsonResponse($result);
    }

}