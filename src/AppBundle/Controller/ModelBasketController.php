<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Entity\Quotation;
use AppBundle\Entity\Order;
use AppBundle\Entity\Setting;
use AppBundle\Service\ProjectManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * @Route("/model-panier")
 */
class ModelBasketController extends Controller
{
    /**
     * @Route("/", name="model_basket_form")
     *
     * @return Response
     */
    public function formAction()
    {
        return $this->render('front/model/form.html.twig', array(
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'email_contact' => $this->getParameter('email_from_address')
        ));
    }
}