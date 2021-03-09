<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Maker;
use AppBundle\Entity\Printer;
use AppBundle\Entity\PrinterRefRequest;
use AppBundle\Form\PrinterRefRequestType;
use AppBundle\Form\PrinterType;
use AppBundle\Service\Mailer;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/%app.user_directory%/mes-imprimantes")
 * @Security("has_role('ROLE_MAKER')")
 */
class UserPrinterController extends Controller
{
    /**
     * @Route("/", name="user_printer_list")
     *
     * @return Response
     */
    public function listAction()
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        // get the printers
        // TODO create a repository method to properly fetch and order the printers
        $printers = $maker->getPrinters();

        // render the list
        return $this->render('front/user/printer/list.html.twig', array('printers' => $printers));
    }

    /**
     * @Route("/ajouter", name="user_printer_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function addAction(Request $request, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        // initialize the printer with a default technology
        $printer = new Printer();
        $technologies = $entityManager->getRepository('AppBundle:Technology')->findAll();
        $defaultTechnology = $technologies[0];
        $printer->setTechnology($defaultTechnology);

        // create the form
        $form = $this->createForm(PrinterType::class, $printer);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            // persist via cascading
            $maker->addPrinter($printer);
            $entityManager->flush();

            // redirect
            $this->addFlash('success', 'printer.flash.created');
            return $this->redirectToRoute('user_printer_edit', array('id' => $printer->getId()));
        }

        // render the form
        return $this->render('front/user/printer/form.html.twig', array('form' => $form->createView(),'maker' => $maker));
    }

    /**
     * @Route("/{id}/modifier", requirements={"id" = "\d+"}, name="user_printer_edit")
     *
     * @param Printer $printer
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function editAction(Printer $printer, Request $request, ObjectManager $entityManager)
    {
        // check user rights
        if ($printer->getMaker() !== $this->getUser()->getMaker()) {
            throw new NotFoundHttpException();
        }

        // create the form
        $form = $this->createForm(PrinterType::class, $printer);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            // flush
            $entityManager->flush();

            // redirect
            $this->addFlash('success', 'printer.flash.updated');
            return $this->redirectToRoute('user_printer_edit', array('id' => $printer->getId()));
        }

        // render the form
        return $this->render('front/user/printer/form.html.twig', array('form' => $form->createView(), 'printer' => $printer));
    }

    /**
     * @Route("/demande-ajout-referentiel", name="user_printer_ref_request")
     *
     * @param Request $request
     * @param Mailer $mailer
     * @return Response
     */
    public function refRequestAction(Request $request, Mailer $mailer)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        // create the form
        $refRequest = new PrinterRefRequest();
        $refRequest->setType(PrinterRefRequest::TYPE_TECHNOLOGY);
        $form = $this->createForm(PrinterRefRequestType::class, $refRequest);
        $form->handleRequest($request);

        // handle the form
        if ($form->isSubmitted() && $form->isValid()) {

            // create a notification message
            $subject = 'Demande d\'ajout dans le référentiel ' . $refRequest->getReadableType();
            $body  = '<b>Maker</b> : ' . $maker->getFullname() . ' (ID : ' . $maker->getId() . ')';
            $body .= '<br>';
            $body .= '<b>Type</b> : ' . $refRequest->getReadableType();
            $body .= '<br>';
            $body .= '<b>Nom</b> : ' . $refRequest->getName();
            if (null !== $refRequest->getDescription()) {
                $body .= '<br>';
                $body .= '<b>Description</b> : ' . $refRequest->getDescription();
            }
            if (PrinterRefRequest::TYPE_TECHNOLOGY === $refRequest->getType()) {
                $body .= '<br>';
                $body .= '<b>Gestion du taux de remplissage</b> : ';
                $body .= $refRequest->hasFillingRate() ? 'Oui' : 'Non';
            }
            if (PrinterRefRequest::TYPE_MATERIAL === $refRequest->getType() && 0 < count($refRequest->getTechnologies())) {
                $body .= '<br>';
                $body .= '<b>Technologies liées</b> : ';
                $i = 0;
                foreach ($refRequest->getTechnologies() as $tech) {
                    if ($i > 0) { $body .= ', '; }
                    $body .= $tech->getName();
                    $i++;
                }
            }
            if (null !== $refRequest->getComments()) {
                $body .= '<br>';
                $body .= '<b>Commentaires</b> : ' . $refRequest->getComments();
            }

            // send the notification message to the configured admin address
            $message = $mailer->createAdminNotificationMessage($subject, $body);
            $mailer->send($message);

            // redirect
            $this->addFlash('success', 'Votre demande a bien été envoyée à United 3D Makers. Un responsable vous informera par e-mail dès la mise en place des nouvelles références, dans les plus brefs délais.');
            return $this->redirectToRoute('user_printer_ref_request');
        }

        // render the form
        return $this->render('front/user/printer/ref_request.html.twig', array('form' => $form->createView(), 'maker' => $maker));
    }

    /**
     * @Route("/{id}/supprimer", requirements={"id" = "\d+"}, name="user_printer_delete")
     *
     * @param Printer $printer
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function deleteAction(Printer $printer, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        if($printer->getMaker() == $maker){

            $entityManager->remove($printer);
            $entityManager->flush();
            $this->addFlash('success', 'Imprimante supprimée avec succès');

            return $this->redirectToRoute('user_printer_list');

        } else {

            $this->addFlash('danger', 'Erreur lors de la suppression');

            return $this->redirectToRoute('user_printer_list');


        }

    }

    /**
     * @Route("/{id}/dupliquer", requirements={"id" = "\d+"}, name="user_printer_clone")
     *
     * @param Printer $printer
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function cloneAction(Printer $printer, ObjectManager $entityManager)
    {
        /** @var Maker $maker */
        $maker = $this->getUser()->getMaker();

        if($printer->getMaker() == $maker){

            $newPrinter = clone $printer;
            $newPrinter->setId(null);
            $newPrinter->setModel($printer->getModel().' - copie');
            $newPrinter->setAvailable(false);

            foreach ($newPrinter->getProducts() as $printerData) {
                    
                $product = clone $printerData;

                foreach ($printerData->getFinishings() as $option) {

                    $finition = clone $option;
                    $finition->setId(null);
                    $product->addFinishing($finition);
                }

                $product->setId(null);

                $newPrinter->addProduct($product);

            }

            $entityManager->persist($newPrinter);
            $entityManager->flush();

            $this->addFlash('success', 'Imprimante dupliquée avec succès');

            return $this->redirectToRoute('user_printer_list');

        } else {

            $this->addFlash('danger', 'Erreur lors de la duplication');

            return $this->redirectToRoute('user_printer_list');


        }

    }


}