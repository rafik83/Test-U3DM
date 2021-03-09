<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Color;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ColorController extends Controller
{
    /**
     * Called by Select2Entity Bundle Ajax method (example in PrinterProductType).
     * Returns a JSON array to fit Select2Entity Bundle specifications: [id: "<id>", text: "<text>"].
     * @see https://github.com/tetranz/select2entity-bundle
     *
     * @Route("/color/select2entity/ajax/list", name="color_select2entity_ajax_list")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return JsonResponse
     */
    public function select2AjaxListAction(Request $request, ObjectManager $entityManager)
    {
        $result = array();

        // only accessible through an Ajax call
        if ($request->isXmlHttpRequest()) {

            // select2entity request
            $like = $request->get('q');

            // get the requested colors
            $colors = $entityManager->getRepository('AppBundle:Color')->findColorsLike($like);

            // create the proper JSON array
            foreach ($colors as $color) {
                /** @var Color $color */
                $result[] = array('id' => $color->getId(), 'text' => $color->getName());
            }
        }

        return new JsonResponse($result);
    }
}