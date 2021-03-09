<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Entity\Quotation;
use AppBundle\Entity\Setting;
use AppBundle\Service\ProjectManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * @Route("/covid")
 */
class CovidController extends Controller
{
    /**
     * @Route("/{reference}", name="covid_form", defaults={"reference"=null})
     *
     * @param $reference
     * @param ObjectManager $entityManager
     * @param ProjectManager $projectManager
     * @return Response
     */
    public function formAction($reference = null,ObjectManager $entityManager,ProjectManager $projectManager,CacheManager $liipImagineCacheManager)
    {

        $stateProject = 'open';
        $jsonProject = null;
        $jsonMakers = null;
        $expiredValidityDate = false;
        $expiredDate = false;
        $tagSpecial="";


        if($reference != null){

            // if the user is not logged in, set the target path in session and redirect to the login page
            if (null === $this->getUser()) {
                $this->container->get('session')->set('_security.main.target_path', $this->generateUrl('design_form', array('reference' => $reference), RouterInterface::ABSOLUTE_URL));
                return $this->redirectToRoute('user_login');
              
            }

            $project = $entityManager->getRepository('AppBundle:Project')->findOneBy(array('customer' => $this->getUser(), 'reference' => $reference));

            if(!$project){

                // if the project is not found for the current logged in user, redirect to the homepage
                return $this->redirect($this->getParameter('www_base_url'));

            } else {

                $jsonProject = $projectManager->projectToJson($project);
                $tagSpecial=$project.gettype().gettagSpec();
                if($project->getStatus() != Project::STATUS_CREATED){
                    $stateProject = 'close';
                }

                //Check if order waiting sepa exist
                $order = $entityManager->getRepository('AppBundle:Order')->findOrdersForProjectWithSepaAwaiting($project);

                // get the closing date if the project is sent (awaiting admin dispatch to makers)
                if (Project::STATUS_SENT === $project->getStatus()) {
                    $expiredDate = $project->getClosedAt();
                }

                if($project->getStatus() == Project::STATUS_DISPATCHED && count($order) == 0){
                    
                    // Find Quotations and Makers
                    // Quotation with STATUS_DISPATCHED = 5; // diffusé
                    $quotations = $entityManager->getRepository('AppBundle:Quotation')->findBy(array('project' => $project, 'status' => Quotation::STATUS_DISPATCHED));

                    $makers = [];

                    $addDayClosed = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::QUOTATION_AGREEMENT_TIME)->getValue().'D';

                    $quotationValidity = $project->getClosedAt()->add(new \DateInterval($addDayClosed));

                    //Expired validity date
                    $now = new \DateTime("now");
                    if($now > $quotationValidity){

                        $expiredValidityDate = true;

                    } else {

                        $expiredValidityDate = false;

                    }

                    $expiredDate = $project->getClosedAt();

                    //Foreach Maker
                    foreach ($quotations as $quotation) {

                            $maker = $quotation->getMaker();
                            
                            $makers[$maker->getId()] = array(
                                'name'        => $maker->getFullname(),
                                'id'        => $maker->getId(),
                                'productions' => count($entityManager->getRepository('AppBundle:Order')->findOrdersForMaker($maker)),
                                'pictures'    => array(
                                    'profile'   => null,
                                    'portfolio' => null
                                ),
                                'quotation' => array(
                                    'id' => $quotation->getId(),
                                    'internal_reference' => $quotation->getInternalReference(),
                                    'quotation_validity' => date_format($quotationValidity, 'd/m/Y'),
                                    'reference' => $quotation->getReference(),
                                    'description' => $quotation->getDescription(),
                                    'production_time' => $quotation->getProductionTime(),
                                    'link_quotation_see' => $this->generateUrl('quotation_customer_see',array('reference' => $quotation->getReference()),UrlGeneratorInterface::ABSOLUTE_URL),
                                    'download_pdf' => $this->generateUrl('quotation_download',array('reference' => $quotation->getReference()),UrlGeneratorInterface::ABSOLUTE_URL),
                                    'lines' => null
                                ),
                                'pickup'      => array(
                                    'available' => $maker->hasPickup(),
                                ),
                                'price_tax_excl' => $quotation->getTotalPrice(),
                                'price_tax_incl' => $quotation->getTotalPrice()+$quotation->getVatPrice(),
                            );

                            // add bio
                            if (null !== $maker->getBio()) {
                                $makers[$maker->getId()]['bio'] = nl2br($maker->getBio());
                            }

                            // add rating
                            if (null !== $maker->getRating()) {
                                $makers[$maker->getId()]['rating'] = $maker->getRating();
                            }

                            // add comment
                            if (null !== $maker->getRatings()) {

                                $makers[$maker->getId()]['comments'] = [];

                                $comments = [];

                                foreach ($maker->getRatings() as $comment){

                                    $content = [];
                                    
                                    if($comment->getEnabled()){

                                        $content['rate'] = $comment->getRate();
                                        $content['comment'] = $comment->getComment();
                                        $content['date'] = $comment->getCreatedAt()->format('d/m/Y');

                                        array_push($comments, $content);

                                    }

                                }

                                $comments = array_reverse($comments);

                                $makers[$maker->getId()]['comments'] = $comments;

                            }

                            // add pictures paths
                            if (null !== $maker->getProfilePictureName()) {
                                $makers[$maker->getId()]['pictures']['profile'] = $liipImagineCacheManager->getBrowserPath($maker->getProfilePictureName(), 'maker_profile');
                            }
                            if (0 < count($maker->getPortfolioImages())) {
                                $makers[$maker->getId()]['pictures']['portfolio'] = array();
                                $portfolio = $maker->getPortfolioImages();
                                foreach ($portfolio as $img) {
                                    $makers[$maker->getId()]['pictures']['portfolio'][] = $liipImagineCacheManager->getBrowserPath($img->getPictureName(), 'maker_portfolio');
                                }
                            }

                            // add pickup address
                            if ($maker->hasPickup()) {
                                $pickupAddress = $maker->getPickupAddress();
                                if (null !== $pickupAddress) {
                                    $makers[$maker->getId()]['pickup']['address'] = array(
                                        'firstname' => $pickupAddress->getFirstname(),
                                        'lastname'  => $pickupAddress->getLastname(),
                                        'company'   => $pickupAddress->getCompany(),
                                        'street1'   => $pickupAddress->getStreet1(),
                                        'street2'   => $pickupAddress->getStreet2(),
                                        'zipcode'   => $pickupAddress->getZipcode(),
                                        'city'      => $pickupAddress->getCity(),
                                        'country'   => $pickupAddress->getCountry(),
                                        'telephone' => $pickupAddress->getTelephone()
                                    );
                                    $makers[$maker->getId()]['pickup_address'] = array(
                                        'firstname' => $pickupAddress->getFirstname(),
                                        'lastname'  => $pickupAddress->getLastname(),
                                        'company'   => $pickupAddress->getCompany(),
                                        'street1'   => $pickupAddress->getStreet1(),
                                        'street2'   => $pickupAddress->getStreet2(),
                                        'zipcode'   => $pickupAddress->getZipcode(),
                                        'city'      => $pickupAddress->getCity(),
                                        'country'   => $pickupAddress->getCountry(),
                                        'telephone' => $pickupAddress->getTelephone()
                                    );
                                }
                            }

                            // add lines
                            if(0 < count($quotation->getLines())){
                                foreach ($quotation->getLines() as $line) {
                                    $makers[$maker->getId()]['quotation']['lines'][] = array(
                                        'number'      => $line->getNumber(),
                                        'description' => $line->getDescription(),
                                        'quantity'    => $line->getQuantity(),
                                        'price'       => $line->getPrice(),
                                        'total'       => $line->getPrice() * $line->getQuantity()
                                    );
                                }
                            }
                        
                    }


                    if(count($makers) > 0){
                        $jsonMakers = json_encode($makers, JSON_FORCE_OBJECT);
                    } else {
                        $jsonMakers = null;
                    }
                    
                }

            }

        }

        return $this->render('front/covid/form.html.twig', array(
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'email_contact' => $this->getParameter('email_from_address'),
            'project' => $jsonProject,
            'state_project' => $stateProject,    
            'tagSpec' => "COVID",
            'makers' => $jsonMakers,
            'expiredValidityDate' => $expiredValidityDate,
            'expiredDate' => $expiredDate
        ));
    }

    /**
     * @Route("/test", name="design_test")
     *
     * @return Response
     */
    public function testAction()
    {
        return $this->render('front/print/test.html.twig');
    }
}