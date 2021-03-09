<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Maker;
use AppBundle\Entity\Scanner;
use AppBundle\Form\ScannerType;
use AppBundle\Form\DesignerParameterType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/%app.user_directory%/mes-scanners")
 * @Security("has_role('ROLE_MAKER')")
 */
class UserScannerController extends Controller
{
    /**
     * @Route("/", name="user_scanner_list")
     *
     * @return Response
     */
    public function listAction()
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        // get the scanners
        $scanners = $maker->getScanners();

        // render the list
        return $this->render('front/user/scanner/list.html.twig', array('scanners' => $scanners));
    }

    /**
     * @Route("/ajouter", name="user_scanner_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function addAction(Request $request, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        // initialize the scanner
        $scanner = new Scanner();

        $technologies = $entityManager->getRepository('AppBundle:TechnologyScanner')->findAll();
        $defaultTechnology = $technologies[0];
        $scanner->setTechnology($defaultTechnology);

        $precisions = $entityManager->getRepository('AppBundle:Precision')->findAll();
        $defaultPrecision = $precisions[0];
        $scanner->setPrecision($defaultPrecision);

        $resolutions = $entityManager->getRepository('AppBundle:Resolution')->findAll();
        $defaultResolution = $resolutions[0];
        $scanner->setResolution($defaultResolution);

        // create the form
        $form = $this->createForm(ScannerType::class, $scanner);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {


            $min = $scanner->getMinDimensions();
            $volumeMin = $min->getX() * $min->getY() * $min->getZ();
            $max = $scanner->getMaxDimensions();
            $volumeMax = $max->getX() * $max->getY() * $max->getZ();
            
            if($volumeMin > $volumeMax){

                $scanner->setMinDimensions($max);
                $scanner->setMaxDimensions($min);

            }

            // persist via cascading
            $maker->addScanner($scanner);
            $entityManager->flush();

            // redirect
            $this->addFlash('success', 'scanner.flash.created');
            return $this->redirectToRoute('user_scanner_edit', array('id' => $scanner->getId()));
        }

        // render the form
        return $this->render('front/user/scanner/form.html.twig', array('form' => $form->createView(), 'maker' => $maker));
    }

    /**
     * @Route("/{id}/modifier", requirements={"id" = "\d+"}, name="user_scanner_edit")
     *
     * @param Scanner $scanner
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function editAction(Scanner $scanner, Request $request, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();
        // check user rights

        if ($scanner->getMaker() !== $this->getUser()->getMaker()) {
            throw new NotFoundHttpException();
        }

        // create the form
        $form = $this->createForm(ScannerType::class, $scanner);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            $min = $scanner->getMinDimensions();
            $volumeMin = $min->getX() * $min->getY() * $min->getZ();
            $max = $scanner->getMaxDimensions();
            $volumeMax = $max->getX() * $max->getY() * $max->getZ();

            if($volumeMin > $volumeMax){

                $scanner->setMinDimensions($max);
                $scanner->setMaxDimensions($min);

            }

            // flush
            $entityManager->flush();

            // redirect
            $this->addFlash('success', 'scanner.flash.updated');
            return $this->redirectToRoute('user_scanner_edit', array('id' => $scanner->getId()));
        }

        // render the form
        return $this->render('front/user/scanner/form.html.twig', array('form' => $form->createView(), 'scanner' => $scanner, 'maker' => $maker));
    }

    /**
     * @Route("/{id}/supprimer", requirements={"id" = "\d+"}, name="user_scanner_delete")
     *
     * @param Scanner $scanner
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function deleteAction(Scanner $scanner, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        if($scanner->getMaker() == $maker){

            $entityManager->remove($scanner);
            $entityManager->flush();
            $this->addFlash('success', 'Scanner supprimé avec succès');

            return $this->redirectToRoute('user_scanner_list');

        } else {

            $this->addFlash('danger', 'Erreur lors de la suppression');

            return $this->redirectToRoute('user_scanner_list');


        }

    }

    /**
     * @Route("/{id}/dupliquer", requirements={"id" = "\d+"}, name="user_scanner_clone")
     *
     * @param Scanner $scanner
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function cloneAction(Scanner $scanner, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        if($scanner->getMaker() == $maker){

            $newScanner = clone $scanner;
            $newScanner->setId(null);
            $newScanner->setName($scanner->getName().' - copie');
            $newScanner->setVisible(false);

            $entityManager->persist($newScanner);
            $entityManager->flush();

            $this->addFlash('success', 'Scanner dupliqué avec succès');

            return $this->redirectToRoute('user_scanner_list');

        } else {

            $this->addFlash('danger', 'Erreur lors de la duplication');

            return $this->redirectToRoute('user_scanner_list');
        }

    }

    /**
     * @Route("/parametres", name="user_scanner_parameters")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function parameterAction(Request $request, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        // create the form
        $form = $this->createForm(DesignerParameterType::class, $maker);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            // persist via cascading
            $entityManager->flush();

            // redirect
            $this->addFlash('success', 'scanner.parameter.updated');
            return $this->redirectToRoute('user_scanner_parameters');
        }

        // render the form
        return $this->render('front/user/scanner/parameter.html.twig', array('form' => $form->createView(), 'maker' => $maker));
    }


}