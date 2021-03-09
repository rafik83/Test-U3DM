<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Message;
use AppBundle\Entity\Order;
use AppBundle\Event\OrderEvent;
use AppBundle\Event\ModelEvent;
use AppBundle\Event\ModelEvents;
use AppBundle\Event\SignalEvent;
use AppBundle\Event\SignalEvents;
use AppBundle\Event\ModelCommentsEvent;
use AppBundle\Event\ModelCommentsEvents;
use AppBundle\Service\OrderManager;
use AppBundle\Entity\Rating;
use AppBundle\Entity\Model;
use AppBundle\Entity\Suggestion;
use AppBundle\Entity\Signal;
use AppBundle\Entity\ModelComments;
use AppBundle\Form\RatingType;
use AppBundle\Form\MakerAddModelType;
use AppBundle\Form\ModelCorrectionType;
use AppBundle\Form\AdminModelType;
use AppBundle\Form\SuggestionType;
use AppBundle\Form\SignalType;
use AppBundle\Form\ModelCommentType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * @Route("/%app.admin_directory%/model")
 */
class AdminModelController extends Controller
{
    /**
     * @Route("/list", name="admin_model_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listAction(ObjectManager $entityManager)
    {
        $chekOrNot = ['checked','','','','',''];
        $models = $entityManager->getRepository('AppBundle:Model')->findModelsFromStatus(1);
        //$models = $entityManager->getRepository('AppBundle:Model')->findAll();
        return $this->render('admin/model/list.html.twig', array(
            'models' => $models,
            'chekOrNot' => $chekOrNot));
    }

    /**
     * @Route("/list/status", name="admin_model_list_status")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function listStatusAction(ObjectManager $entityManager)
    {
        $status_model = array();
        $chekOrNot = ['','','','','',''];
        if( isset($_POST['prenom']) ) {
            foreach($_POST['prenom'] as $valeur) {
                $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFromStatus($valeur);
                $status_model = array_merge($status_model,$models_find);
    
                $chekOrNot[(intval($valeur)-1)] = 'checked';
                //echo("<script>console.log('PHP: ".$valeur."');</script>");
            }
        }
        
        //$models = $entityManager->getRepository('AppBundle:Model')->findAll();
        return $this->render('admin/model/list.html.twig', array(
            'models' => $status_model,
            'chekOrNot' => $chekOrNot));
    }

    /**
     * @Route("/model/modifier-modele-3D/{id}", name="admin_model_modify")
     *
     * @param Model $model
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function detailModelAction(Model $model, Request $request, ObjectManager $entityManager)
    {
        $signalExist = false;
        foreach($model->getModelSignal() as $signal) {
            if($signal->getStatus() === Signal::STATUS_VALID ) {
                $signalExist = true;
            }
        }

        return $this->render('admin/model/formModel.html.twig', array(
            'model' => $model,
            'signalExist' => $signalExist
        ));
        
    }

    /**
     * @Route("/model/modifier-modele-3D/divSuggest/{id}", name="admin_div_suggest_model")
     *
     * @param Model $model
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function detaildevSuggestModelAction(Model $model, Request $request, ObjectManager $entityManager)
    {
        $suggestions = $entityManager->getRepository('AppBundle:Suggestion')->findSuggestionForModel($model);

        if(sizeof($suggestions) < 1) {
            $suggest = new Suggestion();
            $suggest->setModel($model);
        } else {
            $suggest = $suggestions[0];
        }

        
        $form = $this->createForm(SuggestionType::class, $suggest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'La suggestion à été mise à jour');
            $entityManager->persist($suggest);
            $entityManager->flush();
            return $this->redirectToRoute('admin_suggest');
        }

        return $this->render('admin/model/divSuggestModel.html.twig', array(
            'form' => $form->createView()
        ));
        
    }

    /**
     * @Route("/model/modifier-modele-3D/div/{id}", name="div_model")
     *
     * @param Model $model
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function detailDevModelAction(Model $model, Request $request, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $form = $this->createForm(AdminModelType::class, $model);
        $form->handleRequest($request);

        $formCorrection = $this->createForm(ModelCorrectionType::class, $model);
        $formCorrection->handleRequest($request);

        $status = $entityManager->getRepository('AppBundle:ModelStatus')->findAll();
        echo("<script>console.log('PHP: ');</script>");
    
        if (($form->isSubmitted() && $form->isValid()) || ($formCorrection->isSubmitted() && $formCorrection->isValid())) {
            $btn = $request->request->get('add');
            //echo("<script>console.log('PHP: ".$password."');</script>");
            if ('Publier' === $btn) {
                //$model->setMaker($maker);
                $priceTaxExcl = $model->getPriceTaxExcl();

                
                $model->setPriceTaxIncl($priceTaxExcl*1.2);
                $model->setStatus($status[1]);

                $model->setCorrectionReason(null);

                // flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($model);

                $signals = $model->getModelSignal();
                foreach($signals as $signal) {
                    $signal->setStatus(Signal::STATUS_FINISHED);
                    $em->persist($signal);
                }
                $em->flush();

                

                // redirect
                $this->addFlash('success', 'model.admin.flash.validated');
                return $this->redirectToRoute('admin_model_list');
            }
            elseif ('Demander une correction' === $btn) {
                //$model->setMaker($maker);
                $priceTaxExcl = $model->getPriceTaxExcl();

    
                $model->setPriceTaxIncl($priceTaxExcl*1.2);
                


                if ($model->getCorrectionReason() === Null) {
                    // flush
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($model);
                    $em->flush();

                    $signalExist = false;
                    foreach($model->getModelSignal() as $signal) {
                        if($signal->getStatus() === Signal::STATUS_VALID ) {
                            $signalExist = true;
                        }
                    }
                    
                    return $this->render('admin/model/formModel.html.twig', array(
                        'model' => $model,
                        'correctionMsg' => true,
                        'signalExist' => $signalExist
                    ));
                } else {
                    $model->setStatus($status[5]);

                    // flush
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($model);
                    $em->flush();

                    // dispatch an event (as update status would not know the correction context)
                    $eventDispatcher->dispatch(ModelEvents::POST_ADMIN_SENT_TO_CORRECTION, new ModelEvent($model, ModelEvent::ORIGIN_ADMIN));

                    // redirect
                    $this->addFlash('success', 'model.admin.flash.updated');
                    return $this->redirectToRoute('admin_model_list');
                }

                
            }
            elseif ('Supprimer ce modèle' === $btn) {
                //$model->setMaker($maker);
                $priceTaxExcl = $model->getPriceTaxExcl();

    
                $model->setPriceTaxIncl($priceTaxExcl*1.2);

                if ($model->getCorrectionReason() === Null) {
                    // flush
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($model);
                    $em->flush();

                    $signalExist = false;
                    foreach($model->getModelSignal() as $signal) {
                        if($signal->getStatus() === Signal::STATUS_VALID ) {
                            $signalExist = true;
                        }
                    }
                    
                    return $this->render('admin/model/formModel.html.twig', array(
                        'model' => $model,
                        'correctionMsg' => true,
                        'signalExist' => $signalExist
                    ));
                } else {
                    $model->setStatus($status[4]);

                    // flush
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($model);
                    $em->flush();

                    // dispatch an event (as update status would not know the correction context)
                    $eventDispatcher->dispatch(ModelEvents::POST_ADMIN_SENT_TO_DELETE, new ModelEvent($model, ModelEvent::ORIGIN_ADMIN));

                    // redirect
                    $this->addFlash('success', 'Modèle supprimée avec succès');
                    return $this->redirectToRoute('admin_model_list');
                }
            }
        }

        return $this->render('admin/model/divModel.html.twig', array(
            'form' => $form->createView(),
            'formCorrection' => $formCorrection->createView(),
            'model' => $model
        ));
        
    }

    /**
     * @Route("/suggestion", name="admin_suggest")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return Response
     */
    public function suggestAction(ObjectManager $entityManager, Request $request)
    {
        $lastProspects = $entityManager->getRepository('AppBundle:Prospect')->findBy(array(), array('createdAt' => 'DESC'), 5);
        $disabledTags = $entityManager->getRepository('AppBundle:Tag')->findBy(array('enabled' => false));
        $counterProspects = $entityManager->getRepository('AppBundle:Prospect')->countProspects();

        $category = $entityManager->getRepository('AppBundle:Category')->findCategoryOfLevel0();
        $suggestions = [];
        //echo("<script>console.log('PHP:');</script>");
        foreach($category as $cat) {
            //echo("<script>console.log('PHP: 1');</script>");
            $data = [];
            $suggestCategory = $entityManager->getRepository('AppBundle:Suggestion')->findSuggestionForCategory($cat);
            $data['categoryData'] = $cat->getName();
            $data['suggestData'] = $suggestCategory;
            /*
            if(sizeof($suggestCategory) > 0) {
                $suggestModel = $entityManager->getRepository('AppBundle:SuggestionModel')->findSuggestionModelForCategory($cat);
                $data['percentages'] = $suggestModel;
            }*/
            $suggestModel = $entityManager->getRepository('AppBundle:SuggestionModel')->findSuggestionModelForCategory($cat);
            $data['percentages'] = $suggestModel;

            $percentage = 0;
            foreach($suggestModel as $sugg) {
                $percentage += $sugg->getPercentage();
            }
            $data['percentageCategory'] = $percentage;
            array_push($suggestions,$data);
        }
        
        

        return $this->render('admin/model/suggest.html.twig', array(
            'suggestions'      => $suggestions,
            'lastProspects'    => $lastProspects,
            'disabledTags'     => $disabledTags,
            'counterProspects' => $counterProspects
        ));
    }

    /**
     * @Route("/signalement/list", name="admin_signal_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function signalementListAction(ObjectManager $entityManager)
    {
        $signals = $entityManager->getRepository('AppBundle:Signal')->findAllSignalNotValid();
        return $this->render('admin/signal/list.html.twig', array('signals' => $signals));
    }

    /**
     * @Route("/signal/{signal}/enabled", name="admin_signal_enabled")
     *
     * @param ObjectManager $entityManager
     * @param Signal $signal
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function enabledSignalAction(Signal $signal, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {

        $signal->setEnabled(true);
        $signal->setStatus(Signal::STATUS_VALID);

        $entityManager->persist($signal);
        //$entityManager->flush();

        $model = $signal->getModel();
        $status = $entityManager->getRepository('AppBundle:ModelStatus')->findAll();
        $model->setStatus($status[2]);
        $entityManager->persist($model);
        $entityManager->flush();


        // dispatch an event (as update status would not know the correction context)
        $eventDispatcher->dispatch(SignalEvents::POST_ADMIN_SENT_TO_SIGNAL, new SignalEvent($signal, SignalEvent::ORIGIN_ADMIN));
        $eventDispatcher->dispatch(SignalEvents::POST_ADMIN_SENT_TO_CUSTOMER_SIGNAL, new SignalEvent($signal, SignalEvent::ORIGIN_ADMIN));

        $this->addFlash('success', 'Signal validé');

        return $this->redirectToRoute('admin_signal_list');
    }

    /**
     * @Route("/signal/{signal}/delete", name="admin_signal_delete")
     *
     * @param ObjectManager $entityManager
     * @param Signal $signal
     * @return Response
     */
    public function deleteSignalAction(Signal $signal, ObjectManager $entityManager)
    {
        $signal->setStatus(Signal::STATUS_UNVALID);
        //$entityManager->remove($signal);
        $entityManager->persist($signal);
        $entityManager->flush();

        $this->addFlash('success', 'Signal supprimé');

        return $this->redirectToRoute('admin_signal_list');
    }

    /**
     * @Route("/signal/{signal}/edit", requirements={"signal" = "\d+"}, name="admin_signal_edit")
     *
     * @param Signal $signal
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function signalEditAction(Signal $signal, Request $request, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {

        $form = $this->createForm(SignalType::class, $signal,['admin_user'=> true]);
        $form->handleRequest($request);
        //$form->remove('save');

        if ($form->isSubmitted() && $form->isValid()) {
            

            if($signal->getEnabled() === true) {
                $model = $signal->getModel();
                $status = $entityManager->getRepository('AppBundle:ModelStatus')->findAll();
                $model->setStatus($status[2]);
                $entityManager->persist($model);
                $entityManager->flush();

                $signal->setStatus(Signal::STATUS_VALID);

                // dispatch an event (as update status would not know the correction context)
                $eventDispatcher->dispatch(SignalEvents::POST_ADMIN_SENT_TO_SIGNAL, new SignalEvent($signal, SignalEvent::ORIGIN_ADMIN));
                $eventDispatcher->dispatch(SignalEvents::POST_ADMIN_SENT_TO_CUSTOMER_SIGNAL, new SignalEvent($signal, SignalEvent::ORIGIN_ADMIN));
            }

            $entityManager->persist($signal);
            $entityManager->flush();
            $this->addFlash('success', 'Signal validé');
            
            return $this->redirectToRoute('admin_signal_list');
        }

        return $this->render('admin/signal/form.html.twig', array('form' => $form->createView(), 'signal' => $signal));
    }

    /**
     * @Route("/comment/{comment}/enabled", name="admin_comment_enabled")
     *
     * @param ObjectManager $entityManager
     * @param ModelComments $comment
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function enabledCommentAction(ModelComments $comment, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {

        $comment->setEnabled(true);

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire client validé');

        if($comment->getModel()->getMaker()->getUser() != $comment->getCustomer()) {
            // dispatch an event (as update status would not know the correction context)
            $eventDispatcher->dispatch(ModelCommentsEvents::POST_ADMIN_SENT_TO_COMMENT, new ModelCommentsEvent($comment, ModelCommentsEvent::ORIGIN_ADMIN));
        }
        

        return $this->redirectToRoute('admin_comment_list');
    }

    /**
     * @Route("/comment/{comment}/delete", name="admin_comment_delete")
     *
     * @param ObjectManager $entityManager
     * @param ModelComments $comment
     * @return Response
     */
    public function deleteCommentAction(ModelComments $comment, ObjectManager $entityManager)
    {
        $commentsDown = $entityManager->getRepository('AppBundle:ModelComments')->findAllDownCommentNot($comment);
        if(sizeof($commentsDown) > 0) {
            $this->addFlash('danger', 'Commentaire client composé de réponse, ne peut pas être supprimé');
        } else {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire client supprimé');
        }
        return $this->redirectToRoute('admin_comment_list');
    }

    /**
     * @Route("/comment/{comment}/edit", requirements={"comment" = "\d+"}, name="admin_comment_edit")
     *
     * @param ModelComments $comment
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function commentEditAction(ModelComments $comment, Request $request, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {

        $form = $this->createForm(ModelCommentType::class, $comment,['admin_user'=> true]);
        $form->handleRequest($request);
        //$form->remove('save');

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();
            if($comment->getEnabled() === true) {

                if($comment->getModel()->getMaker()->getUser() != $comment->getCustomer()) {
                    // dispatch an event (as update status would not know the correction context)
                    $eventDispatcher->dispatch(ModelCommentsEvents::POST_ADMIN_SENT_TO_COMMENT, new ModelCommentsEvent($comment, ModelCommentsEvent::ORIGIN_ADMIN));
                }
                
            }
            $this->addFlash('success', 'admin.rating.edit.title');
            return $this->redirectToRoute('admin_comment_list');
        }

        return $this->render('admin/modelComment/form.html.twig', array('form' => $form->createView(), 'comment' => $comment));
    }


}
