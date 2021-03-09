<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Prospect;
use AppBundle\Event\ProspectEvent;
use AppBundle\Event\ProspectEvents;
use AppBundle\Form\ProspectCustomerType;
use AppBundle\Form\ProspectMakerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class ProspectController extends Controller
{
    /**
     * @Route("/inscription", name="prospect")
     *
     * @param Request $request
     * @param EventDispatcherInterface $eventDispatcher
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function prospectAction(Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $prospect = new Prospect();

        $formCustomer = $this->createForm(ProspectCustomerType::class, $prospect);
        $formCustomer->handleRequest($request);

        if ($formCustomer->isSubmitted() && $formCustomer->isValid()) {

            // dispatch an Event
            $eventDispatcher->dispatch(ProspectEvents::PRE_PERSIST, new ProspectEvent($prospect));

            // persist
            $em = $this->getDoctrine()->getManager();
            $em->persist($prospect);
            $em->flush();

            // dispatch an Event
            $eventDispatcher->dispatch(ProspectEvents::POST_PERSIST, new ProspectEvent($prospect));

            // redirect to the registration form with the prospect token argument
            return $this->redirectToRoute('user_register', array('prospectToken' => $prospect->getToken()));

        }

        return $this->render('front/prospect/form.html.twig', array(
            'formCustomer' => $formCustomer->createView()
        ));
    }

    /**
     * @Route("/inscription-maker", name="prospect_maker")
     *
     * @param Request $request
     * @param EventDispatcherInterface $eventDispatcher
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function prospectMakerAction(Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $prospect = new Prospect();

        $formMaker = $this->createForm(ProspectMakerType::class, $prospect);
        $formMaker->handleRequest($request);

        if ($formMaker->isSubmitted() && $formMaker->isValid()) {

            // dispatch an Event
            $eventDispatcher->dispatch(ProspectEvents::PRE_PERSIST, new ProspectEvent($prospect));

            // persist
            $em = $this->getDoctrine()->getManager();
            $em->persist($prospect);
            $em->flush();

            // dispatch an Event
            $eventDispatcher->dispatch(ProspectEvents::POST_PERSIST, new ProspectEvent($prospect));

            // redirect to the registration form with the prospect token argument
            return $this->redirectToRoute('user_register', array('prospectToken' => $prospect->getToken()));

        }

        return $this->render('front/prospect/form_maker.html.twig', array(
            'formMaker'    => $formMaker->createView()
        ));
    }
}