<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TagController extends Controller
{
    /**
     * Called by Select2Entity Bundle Ajax method (example in ProspectType).
     * Returns a JSON array to fit Select2Entity Bundle specifications: [id: "<id>", text: "<text>"].
     * @see https://github.com/tetranz/select2entity-bundle
     *
     * @Route("/tag/select2entity/ajax/list", name="tag_select2entity_ajax_list")
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
            $type = $request->get('type');

            // get the requested tags
            $tags = $entityManager->getRepository('AppBundle:Tag')->findTagsForTypeAndLike($type, $like);

            // create the proper JSON array
            foreach ($tags as $tag) {
                /** @var Tag $tag */
                $result[] = array('id' => $tag->getId(), 'text' => $tag->getName());
            }
        }

        return new JsonResponse($result);
    }
}