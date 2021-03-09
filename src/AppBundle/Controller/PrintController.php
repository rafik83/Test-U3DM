<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/impression")
 */
class PrintController extends Controller
{
    /**
     * @Route("/", name="print_form")
     *
     * @return Response
     */
    public function formAction()
    {
        return $this->render('front/print/form.html.twig', array(
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'email_contact' => $this->getParameter('email_from_address')
        ));
    }

    /**
     * @Route("/test", name="print_test")
     *
     * @return Response
     */
    public function testAction()
    {
        return $this->render('front/print/test.html.twig');
    }
}