<?php

namespace AppBundle\Controller;

use AppBundle\Service\SendinBlue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NewsletterController extends Controller
{
    /**
     * Called by the Newsletter form
     * This must be callable from all subdomains (www* and app*)
     *
     * @Route("/newsletter/ajax/subscribe", name="newsletter_ajax_subscribe")
     *
     * @param Request $request
     * @param SendinBlue $sendinBlue
     * @return JsonResponse
     */
    public function ajaxSubscribeAction(Request $request, SendinBlue $sendinBlue)
    {
        $result = array();

        // check that this is an Ajax call - but that would return false when calling from another domain
        // @see https://stackoverflow.com/questions/33799454/cross-domain-ajax-why-isxmlhttprequest-return-false
        // we need to be able to call that from the www*.united-3dmakers.com part of the site, so do not make that check
        //if ($request->isXmlHttpRequest()) {}

        $email = $request->get('email');

        if (null === $email) {

            // if no e-mail in Request, then return the error status code
            $result['status'] = 400;

        } else {

            $sendinBlue->subscribeToNewsletter($email);
            $result['status'] = 201;

        }

        $response = new JsonResponse($result);

        // allow cross origin request
        // @see https://ourcodeworld.com/articles/read/291/how-to-solve-the-client-side-access-control-allow-origin-request-error-with-your-own-symfony-3-api
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}