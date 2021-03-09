<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Entity\Category;
use AppBundle\Entity\Model;
use AppBundle\Entity\Maker;
use AppBundle\Entity\ModelComments;
use AppBundle\Entity\ModelDownload;
use AppBundle\Entity\ModelLove;
use AppBundle\Entity\Suggestion;
use AppBundle\Entity\Signal;
use AppBundle\Form\ModelCommentType;
use AppBundle\Form\SignalType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * @Route("/modele")
 */
class ModelController extends Controller
{
    /**
     * @Route("/", name="model_home")
     *
     * 
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function formAction(ObjectManager $entityManager,CacheManager $liipImagineCacheManager)
    {

        //$models = $entityManager->getRepository('AppBundle:Model')->findModelsValide();
        $categoryLevel0 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel0();
        $categoryLevel1 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel1();

        $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.createdAt', 'DESC');
        $nbModels = sizeof($models);
        $models = array_slice($models, 0, 9);
        foreach ($models as $model) {
            $model->setImageLink($liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'));
        }
        return $this->render('front/model/catalogue.html.twig', array(
            'models' => $models,
            'nbModels' => $nbModels,
            'categoryLevel0' => $categoryLevel0,
            'categoryLevel1' => $categoryLevel1
        ));
    }
    /**
     * @Route("/category/{name}/{id}", name="model_find_by_category")
     *
     * @param Category $category
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function findModelByCategoryAction(Category $category, Request $request, ObjectManager $entityManager,CacheManager $liipImagineCacheManager)
    {
        //$models = $entityManager->getRepository('AppBundle:Model')->findModelsFromCategory($category);

        $categoryLevel0 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel0();
        $categoryLevel1 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel1();
        $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($category->getId(), 'model.createdAt', 'DESC');
        $nbModels = sizeof($models);
        $models = array_slice($models, 0, 9);
        foreach ($models as $model) {
            $model->setImageLink($liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'));
        }
        return $this->render('front/model/catalogue.html.twig', array(
            'models' => $models,
            'nbModels' => $nbModels,
            'categoryLevel0' => $categoryLevel0,
            'categoryLevel1' => $categoryLevel1
        ));
    }

    /**
     * @Route("/Upcategory/{name}/{id}", name="model_find_by_categorySup")
     *
     * @param Category $category
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function findModelByCategorySupAction(Category $category, Request $request, ObjectManager $entityManager,CacheManager $liipImagineCacheManager)
    {
        //$models = $entityManager->getRepository('AppBundle:Model')->findModelsFromCategory($category);

        $categoryLevel0 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel0();
        $categoryLevel1 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel1();
        $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($category->getId(), 'model.createdAt', 'DESC');
        $nbModels = sizeof($models);
        $models = array_slice($models, 0, 9);
        foreach ($models as $model) {
            $model->setImageLink($liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'));
        }
        return $this->render('front/model/catalogue.html.twig', array(
            'models' => $models,
            'nbModels' => $nbModels,
            'categoryLevel0' => $categoryLevel0,
            'categoryLevel1' => $categoryLevel1
        ));
    }

    public function searchBarAction() {
        $form = $this->createFormBuilder(null)
            ->add('search',  TextType::class)
        ->getForm();
        
        return $this->render('front/model/SearchBar.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/maker/{company}/{id}", name="model_find_by_maker")
     *
     * @param Maker $maker
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function findModelByMakerAction(Maker $maker, Request $request, ObjectManager $entityManager,CacheManager $liipImagineCacheManager)
    {

        //$models = $entityManager->getRepository('AppBundle:Model')->findModelsForMaker($maker);

        $categoryLevel0 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel0();
        $categoryLevel1 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel1();
        $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($maker->getId(), 'model.createdAt', 'DESC');
        $nbModels = sizeof($models);
        $models = array_slice($models, 0, 9);
        foreach ($models as $model) {
            $model->setImageLink($liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'));
        }
        return $this->render('front/model/catalogue.html.twig', array(
            'models' => $models,
            'nbModels' => $nbModels,
            'categoryLevel0' => $categoryLevel0,
            'categoryLevel1' => $categoryLevel1
        ));
    }

    /**
     * @Route("/search", name="model_search")
     *
     * 
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function modelSearchAction(Request $request, ObjectManager $entityManager,CacheManager $liipImagineCacheManager)
    {
        $categoryLevel0 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel0();
        $categoryLevel1 = $entityManager->getRepository('AppBundle:Category')->findCategoryValidLevel1();
        
        $url = $_SERVER['REQUEST_URI'];

        $data_search_list = preg_split("/search%5D=|&form/", $url);
        $search_words = $data_search_list[1];
        $each_word = explode("+", $search_words);

        $models = array();
        foreach ($each_word as $word){
            $word=rawurldecode($word);
            if(strtoupper($word) == 'GRATUIT') {
                $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFreeFromWordOrderByDate($word);
            } else {
                $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFromWordOrderByDate($word);    
            }
            
            $models_with_duplicate = array_merge($models,$models_find);

            //this line is just to avoid duplicate of model
            $models = array_unique($models_with_duplicate, SORT_REGULAR);
        }
        
        $nbModels = sizeof($models);
        $models = array_slice($models, 0, 9);
        foreach ($models as $model) {
            $model->setImageLink($liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'));
        }
        return $this->render('front/model/catalogue.html.twig', array(
            'models' => $models,
            'nbModels' => $nbModels,
            'categoryLevel0' => $categoryLevel0,
            'categoryLevel1' => $categoryLevel1
        ));
    }

    /**
     * @Route("/{name}/{id}", name="model_detail")
     *
     * @param Int $id
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function detailModelAction(Int $id, Request $request, ObjectManager $entityManager,CacheManager $liipImagineCacheManager)
    {
        //$intId = intval($id);
        $models = $entityManager->getRepository('AppBundle:Model')->findModelsById($id);

        /** @var Maker $maker */
        $user = $this->getUser();
        if($user === null) {
            $maker = null;
            $baskets = null;
            $sizeBasket = null;
        } else {
            $maker = $user->getMaker();
            $baskets = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findAllModelsInBasketForUser($this->getUser());
            $sizeBasket = sizeof($baskets);
        }
        

        
        //$sizeBasket = sizeof($baskets);
        $model = $models[0];


        
        if($model->getstatus()->getId()===1 || $model->getstatus()->getId()===2  || $maker === $model->getMaker()) {
            
            $portfolio = $model->getPortfolioImages();
            $allMakerModel = $entityManager->getRepository('AppBundle:Model')->findModelsValideForMaker($model->getMaker());

            $totalLove = 0;
            $totalDownload = 0;
            $totalModel = sizeof($allMakerModel);

            foreach ($allMakerModel as $modelMaker) {
                $totalLove = $totalLove + $modelMaker->getLove();
                $totalDownload = $totalDownload + $modelMaker->getNbDownload();
            }

            $images = array();
            foreach ($portfolio as $img) {
                $image = $liipImagineCacheManager->getBrowserPath($img->getPictureName(), 'model_portfolio');
                array_push($images,$image);
            }

            $categoryModel = $entityManager->getRepository('AppBundle:Category')->findCategoryOfModel($model);
            
            //$categoryModelSup = $entityManager->getRepository('AppBundle:Category')->findCategoryUpOfModel($model);
            $suggestionsModelPush = [];
            foreach ($categoryModel as $cat) {
                $suggestModel = $entityManager->getRepository('AppBundle:SuggestionModel')->findSuggestionModelForCategory($cat->getUpCategory());
                array_merge($suggestionsModelPush,$suggestModel);
            }
            $suggestionsPush = [];
            foreach ($categoryModel as $cat) {
                $suggestModel = $entityManager->getRepository('AppBundle:Suggestion')->findSuggestionForCategory($cat->getUpCategory());
                //echo("<script>console.log('Taille suggestModel: " . sizeof($suggestModel) . "');</script>");
                foreach ($suggestModel as $suggest) {
                    $suggestModelOfSuggest = $entityManager->getRepository('AppBundle:SuggestionModel')->findSuggestionModelForCategoryAndModel($cat->getUpCategory(),$suggest->getModel());
                    
                    //echo("<script>console.log('Taille suggestModel: " . sizeof($suggestModelOfSuggest) . "');</script>");
                    $suggestFind = $suggestModelOfSuggest[0];
                    $suggest->setPercentage($suggest->getPercentage() + $suggestFind->getPercentage());
                    //echo("<script>console.log('pourcentage trouvé: " . $suggestFind->getPercentage() . "');</script>");
                    if (in_array($suggest, $suggestionsPush) === false) {
                        array_push($suggestionsPush,$suggest);
                    }
                }
            }
            
            $randomInt = random_int(1, (100*sizeof($categoryModel)));
            $totalpercent = 0;
            $randomSuggest = null;
            foreach ($suggestionsPush as $modeltoPush){
                $modeltoPush->setPercentage($modeltoPush->getPercentage() + $totalpercent);
                $totalpercent = $modeltoPush->getPercentage();
                if($modeltoPush->getPercentage() > $randomInt && $randomSuggest === null) {
                    $randomSuggest = $modeltoPush;
                }
            }

            
            //echo("<script>console.log('random: " . $randomInt . "');</script>");
            //echo("<script>console.log('random: " . $randomSuggest->getModel()->getName() . "');</script>");
            if($randomSuggest !== null) {
                if($randomSuggest->getModel() === $model) {
                    $randomSuggest = null;
                }
            }

            $tags = $model->getTags();
            $tag_list = explode(" ", $tags);

            $suggestion = array();

            foreach ($tag_list as $word){
                $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFromWord($word);
                $suggestion = array_merge($suggestion,$models_find);

                //this line is just to avoid duplicate of model
                //$tag_list = array_unique($models_with_duplicate, SORT_REGULAR);
            }
            $map = function($v) {return $v->getId();};
            $suggestion = array_count_values(array_map($map, $suggestion));
            arsort($suggestion);
            $fiveSuggest = array();

            $imagesSuggest = array();
            $nbOfSuggest = 4;
            if($randomSuggest !== null) {
                $nbOfSuggest = 3;
                array_push($fiveSuggest,$randomSuggest->getModel());
            }
            
            foreach ($suggestion as $key => $val) { 
                if (sizeof($fiveSuggest) > $nbOfSuggest) {
                    break;    /* Vous pourriez aussi utiliser 'break 1;' ici. */
                }
                $model_find = $entityManager->getRepository('AppBundle:Model')->findModelsById($key);
                if ($model_find[0] != $model){
                    
                    $image = $liipImagineCacheManager->getBrowserPath($model_find[0]->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small');
                    $model_find[0]->setImageLink($image);
                    //array_push($imagesSuggest,$image);
                    if($randomSuggest !== null) {
                        if($randomSuggest->getModel() === $model_find[0]) {
                            $nbOfSuggest = 4;
                        } else {
                            array_push($fiveSuggest,$model_find[0]);
                        }
                    } else {
                        array_push($fiveSuggest,$model_find[0]);
                    }
                    
                }
                
                //echo("<script>console.log('key : ".sizeof($model_find)."');</script>");
            }

            //echo("<script>console.log('Test2: ".$model->getMaker()->getProfilePictureName()."');</script>");
            //echo("<script>console.log('Test3: ');</script>");
            $profileImage = $model->getMaker()->getProfilePictureName();
            if($profileImage !== null) {
                $logo = $liipImagineCacheManager->getBrowserPath($profileImage, 'maker_profile');
            } else {
                $logo = null;
            }
            
            //echo("<script>console.log('Test3: ');</script>");

            $comments = $entityManager->getRepository('AppBundle:ModelComments')->findCommentsForModel($model);
            //$commentPortfolio = $comments->getPortfolioImages();
            $formResponseList = array();
            $nbComment = sizeof($comments);
            for ($i = 0; $i < $nbComment; $i++) {
                $responses = $entityManager->getRepository('AppBundle:ModelComments')->findResponseForModel($comments[$i]);
                foreach ($responses as $response) {
                    array_push($comments,$response);
                }
                $commentPortfolio = $comments[$i]->getPortfolioImages();
                foreach ($commentPortfolio as $img) {
                    $imageLink = $liipImagineCacheManager->getBrowserPath($img->getPictureName(), 'comment_portfolio');
                    $img->setImageLink($imageLink);
                }
                //comment form
                $modelComment = new ModelComments();
                $formResponse = $this->createForm(ModelCommentType::class, $modelComment);
                $formResponse->handleRequest($request);
                $formView = $formResponse->createView();
                array_push($formResponseList,$formView);
            }
            /*
            foreach ($comments as $comment) {
                $commentPortfolio = $comment->getPortfolioImages();
                foreach ($commentPortfolio as $img) {
                    $imageLink = $liipImagineCacheManager->getBrowserPath($img->getPictureName(), 'comment_portfolio');
                    $img->setImageLink($imageLink);
                }
                //comment form
                $modelComment = new ModelComments();
                $formResponse = $this->createForm(ModelCommentType::class, $modelComment);
                $formResponse->handleRequest($request);
                $formView = $formResponse->createView();
                array_push($formResponseList,$formView);
            }
            */

            //comment form
            $modelComment = new ModelComments();
            $form = $this->createForm(ModelCommentType::class, $modelComment);
            $form->handleRequest($request);
            if($user === null) {
                $form->remove('save');
            }

            if ($form->isSubmitted() && $form->isValid()) {
                if (isset($_POST['save'])) {
                    $commentUp = $entityManager->getRepository('AppBundle:ModelComments')->find($_POST['save']);
                    $modelComment->setUpComments($commentUp);
                }
                
                $modelComment->setModel($model);
                $modelComment->setCustomer($user);

                // flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($modelComment);
                $em->flush();

                $commentPortfolio = $modelComment->getPortfolioImages();
                foreach ($commentPortfolio as $img) {
                    if ($img->getPictureName() == null) {
                        $entityManager->remove($img);
                        $entityManager->flush();
                    }
                }

                // redirect
                return $this->redirectToRoute('model_detail', array(
                    'id' => $model->getId(),
                    'name' => $model->getName(),
                ));
            }

            //comment form
            $modelCommentResponse = new ModelComments();
            $formResponse = $this->createForm(ModelCommentType::class, $modelCommentResponse);
            $formResponse->handleRequest($request);
            $formResponse->remove('portfolioImages');
            $formResponse->remove('save');

            //Total of comments for this model
            $totalComments = $entityManager->getRepository('AppBundle:ModelComments')->findAllUpCommentsForModel($model);

            $signal = new Signal();
            $signalForm = $this->createForm(SignalType::class, $signal);
            $signalForm->handleRequest($request);

            if ($signalForm->isSubmitted() && $signalForm->isValid()) {
                $signal->setModel($model);
                // flush
                //$em = $this->getDoctrine()->getManager();
                $entityManager->persist($signal);
                $entityManager->flush();

                // redirect
                return $this->redirectToRoute('model_detail', array(
                    'id' => $model->getId(),
                    'name' => $model->getName(),
                ));
            }

            $allreadyLove = array();
            if($user != null) {
                $allreadyLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModelAndUser($model, $user);
            } else {
                $sessionCustomer = session_id();
                if($sessionCustomer != null) {
                    $allreadyLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModelAndSession($model, $sessionCustomer);
                }
                //echo("<script>console.log('PHP: ".$data."');</script>");
            }

            return $this->render('front/model/detail.html.twig', array(
                'signalForm' => $signalForm->createView(),
                'form' => $form->createView(),
                'formResponse' => $formResponse,
                'user' => $user,
                'model' => $model,
                'images' => $images,
                'totalLove' => $totalLove,
                'totalDownload' => $totalDownload,
                'totalComments' => sizeof($totalComments),
                'categoryModel' => $categoryModel,
                'totalModel' => $totalModel,
                'logo' => $logo,
                'sizeBasket' => $sizeBasket,
                'comments' => $comments,
                'fiveSuggest' => $fiveSuggest,
                'allreadyLove' => $allreadyLove
            ));
        } else {
            //echo("<script>console.log('IF: ".$model->getstatus()->getId()."');</script>");

            return $this->render('front/model/detail.hide.html.twig', array(
                'model' => $model
            ));
        }
        
    }

    /**
     * @Route("/{name}/{id}/download", name="model_download")
     *
     * @param Model $model
     * @return BinaryFileResponse
     */
    public function orderFileDownloadAction(Model $model, ObjectManager $entityManager)
    {
        
        
        
        // get file from project directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/model/attachment/' . $model->getAttachmentName());

        /** @var Maker $maker */
        $user = $this->getUser();


        //$userDownload = $entityManager->getRepository('AppBundle:ModelDownload')->findDownloadForModelAndUser($model, $user);

        $modelDownload = new ModelDownload();
        $modelDownload->setModel($model);
        if($user != null) {
            $modelDownload->setCustomer($user);
        } else {
            $sessionCustomer = session_id();
            if($sessionCustomer != null) {
                $modelDownload->setSessionName($sessionCustomer);
            }
            //echo("<script>console.log('PHP: ".$data."');</script>");
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($modelDownload);
        $em->flush();

        $totalDownload = $entityManager->getRepository('AppBundle:ModelDownload')->findDistinctDownloadForModel($model, $entityManager);
        $model->setNbDownload(sizeof($totalDownload));
        // flush
        $em = $this->getDoctrine()->getManager();
        $em->persist($model);
        $em->flush();


        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $model->getAttachmentOriginalName());
        return $response;
    }

    /**
     * @Route("/{name}/{id}/love", name="model_love")
     *
     * @param Model $model
     * @return BinaryFileResponse
     */
    public function orderFileLoveAction(Model $model, ObjectManager $entityManager)
    {
        
        // get file from project directory
        //$response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/model/attachment/' . $model->getAttachmentName());

        /** @var Maker $maker */
        $user = $this->getUser();


        //$userDownload = $entityManager->getRepository('AppBundle:ModelDownload')->findDownloadForModelAndUser($model, $user);

        $modelDownload = new ModelLove();
        $modelDownload->setModel($model);
        if($user != null) {
            $modelDownload->setCustomer($user);
            $allreadyLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModelAndUser($model, $user);
        } else {
            $sessionCustomer = session_id();
            if($sessionCustomer != null) {
                $modelDownload->setSessionName($sessionCustomer);
                $allreadyLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModelAndSession($model, $sessionCustomer);
            }
            //echo("<script>console.log('PHP: ".$data."');</script>");
        }
        //echo("<script>console.log('PHP:1 ".sizeof($allreadyLove)."');</script>");
        if( sizeof($allreadyLove) < 1) {
            //echo("<script>console.log('PHP: 2');</script>");
            // flush
            $em = $this->getDoctrine()->getManager();
            $em->persist($modelDownload);
            $em->flush();

            
        }

        $allLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModel($model);
        //echo("<script>console.log('PHP:Love ".sizeof($allLove)."');</script>");
        $model->setLove(sizeof($allLove));
        // flush
        $em = $this->getDoctrine()->getManager();
        $em->persist($model);
        $em->flush();

        return $this->redirectToRoute('model_detail', array(
            'id' => $model->getId(),
            'name' => $model->getName(),
        ));
    }

    /**
     * @Route("/{name}/{id}/dislove", name="model_dislove")
     *
     * @param Model $model
     * @return BinaryFileResponse
     */
    public function orderFileDisLoveAction(Model $model, ObjectManager $entityManager)
    {
        
        // get file from project directory
        //$response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/model/attachment/' . $model->getAttachmentName());

        /** @var User $maker */
        $user = $this->getUser();


        //$userDownload = $entityManager->getRepository('AppBundle:ModelDownload')->findDownloadForModelAndUser($model, $user);

        //$modelDownload = new ModelLove();
        //$modelDownload->setModel($model);
        if($user != null) {
            //$modelDownload->setCustomer($user);
            $allreadyLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModelAndUser($model, $user);
        } else {
            $sessionCustomer = session_id();
            if($sessionCustomer != null) {
                //$modelDownload->setSessionName($sessionCustomer);
                $allreadyLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModelAndSession($model, $sessionCustomer);
            }
            //echo("<script>console.log('PHP: ".$data."');</script>");
        }
        $loveMade = $allreadyLove[0];
        $entityManager->remove($loveMade);
        $entityManager->flush();

        $allLove = $entityManager->getRepository('AppBundle:ModelLove')->findLoveForModel($model);
        //echo("<script>console.log('PHP:Love ".sizeof($allLove)."');</script>");
        $model->setLove(sizeof($allLove));
        // flush
        $em = $this->getDoctrine()->getManager();
        $em->persist($model);
        $em->flush();

        return $this->redirectToRoute('model_detail', array(
            'id' => $model->getId(),
            'name' => $model->getName(),
        ));
    }

    /**
     * @Route("/{name}/{id}/admindownload", name="model_admin_download")
     *
     * @param Model $model
     * @return BinaryFileResponse
     */
    public function orderFileAdminDownloadAction(Model $model, ObjectManager $entityManager)
    {
        
        
        
        // get file from project directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/maker/model/attachment/' . $model->getAttachmentName());

        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $model->getAttachmentOriginalName());
        return $response;
    }

    /**
     * @Route("/{name}/{id}/AddBasket", name="model_add_basket")
     *
     * @param Model $model
     * @return BinaryFileResponse
     */
    public function orderFileAddBasketAction(Model $model)
    {
        $request = $this->container->get('request');
        $routeName = $request->get('_route');

        return $this->render($routeName);
    }
    
}
