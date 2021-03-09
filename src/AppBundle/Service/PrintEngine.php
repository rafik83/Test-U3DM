<?php

namespace AppBundle\Service;

use AppBundle\Entity\Color;
use AppBundle\Entity\Embeddable\Dimensions;
use AppBundle\Entity\FillingRate;
use AppBundle\Entity\Finishing;
use AppBundle\Entity\Layer;
use AppBundle\Entity\Material;
use AppBundle\Entity\Printer;
use AppBundle\Entity\PrinterProduct;
use AppBundle\Entity\PrinterProductFinishing;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Technology;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Service to search available printing combinations and prices
 */
class PrintEngine
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CacheManager
     */
    private $liipImagineCacheManager;

    /**
     * @var array : makers data
     */
    private $makers;

    /**
     * @var array : ref data ; initialized in the constructor
     */
    private $ref;

    /**
     * @var float : tax rate in percent (example: 20.0 for 20%)
     */
    private $taxRate;


    /**
     * PrinterEngine constructor
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface    $translator
     * @param CacheManager           $liipImagineCacheManager
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, CacheManager $liipImagineCacheManager)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->liipImagineCacheManager = $liipImagineCacheManager;

        // initialize makers array
        $this->makers = array();

        // initialize ref array
        $this->ref = array(
            'technology' => array(),
            'material'   => array(),
            'color'      => array(),
            'layer'      => array(),
            'finishing'  => array(
                0 => array('name' => $this->translator->trans('print_engine.ref.finishing.none'))
            ),
            'filling'    => array(
                FillingRate::NONE => array('name' => $this->translator->trans('print_engine.ref.filling.none'))
            )
        );

        // filling rates
        for ($i = 5 ; $i <= 100 ; $i += 5) {
            $this->ref['filling'][$i] = array('name' => $i . ' %');
        }

        // set tax rate
        $this->taxRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_TAX_RATE)->getValue();
    }

    /**
     * @param float $volume             the object volume in mm3
     * @param Dimensions $dimensions    the object dimensions in mm
     * @param int $quantity             the object quantity
     * @param array $allowedMakers      the optional list of allowed makers (if list is empty: all makers are allowed)
     * @return string                   JSON object of available printing combinations and prices
     */
    public function search($volume, Dimensions $dimensions, $quantity = 1, $allowedMakers = array())
    {
        // combinations array
        $result = array();

        // get the printers

        $volumeBoundingBox = $dimensions->getX()*$dimensions->getY()*$dimensions->getZ();
        
       
        $printers = $this->entityManager->getRepository('AppBundle:Printer')->findAvailablePrintersAcceptingVolume($volume * $quantity, $volumeBoundingBox*$quantity);

        /** @var Printer $printer */
        foreach($printers as $printer) {

            // get the printer maker
            $maker = $printer->getMaker();

            // if a list of allowed makers is provided and is not empty, make sure the maker is part of it
            if (0 !== count($allowedMakers)) {
                if (!in_array($maker->getId(), $allowedMakers)) {
                    continue;
                }
            }

            // check that the total objects volume is bigger than the printer min volume
            if (($volume * $quantity) < $printer->getMinVolume()) {
                continue;
            }

            // check that the printer is available
            if (!$printer->isAvailable()) {
                continue;
            }

            // check that the maker is available
            if (!$maker->isAvailable()) {
                continue;
            }

            // check that the object dimensions fits into the printer max dimensions
            if (!$dimensions->fitsInto($printer->getMaxDimensions())) {
                continue;
            }

            // get maker data for that printer and populate makers array if needed
            if (!array_key_exists($maker->getId(), $this->makers)) {
                $this->makers[$maker->getId()] = array(
                    'name'        => $maker->getFullname(),
                    //'productions' => $maker->getNumberOfProductions(),
                    'productions' => count($this->entityManager->getRepository('AppBundle:Order')->findOrdersForMaker($maker)),
                    'pictures'    => array(
                        'profile'   => null,
                        'portfolio' => null
                    ),
                    'pickup'      => array(
                        'available' => $maker->hasPickup(),
                    )
                );

                // add bio
                if (null !== $maker->getBio()) {
                    $this->makers[$maker->getId()]['bio'] = nl2br($maker->getBio());
                }

                // add rating
                if (null !== $maker->getRating()) {
                    $this->makers[$maker->getId()]['rating'] = $maker->getRating();
                }

                // add comment
                if (null !== $maker->getRatings()) {

                    $this->makers[$maker->getId()]['comments'] = [];

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

                    $this->makers[$maker->getId()]['comments'] = $comments;

                }

                // add pictures paths
                if (null !== $maker->getProfilePictureName()) {
                    $this->makers[$maker->getId()]['pictures']['profile'] = $this->liipImagineCacheManager->getBrowserPath($maker->getProfilePictureName(), 'maker_profile');
                }
                if (0 < count($maker->getPortfolioImages())) {
                    $this->makers[$maker->getId()]['pictures']['portfolio'] = array();
                    $portfolio = $maker->getPortfolioImages();
                    foreach ($portfolio as $img) {
                        $this->makers[$maker->getId()]['pictures']['portfolio'][] = $this->liipImagineCacheManager->getBrowserPath($img->getPictureName(), 'maker_portfolio');
                    }
                }

                // add pickup address
                if ($maker->hasPickup()) {
                    $pickupAddress = $maker->getPickupAddress();
                    if (null !== $pickupAddress) {
                        $this->makers[$maker->getId()]['pickup']['address'] = array(
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
            }

            // set base fillings array, depending if technology has filling rates or not
            $baseFillingsArray = array(
                FillingRate::NONE => array('prices' => array())//no filling rate
            );
            if ($printer->getTechnology()->hasFillingRate()) {
                $baseFillingsArray = array();
                for ($i = 5 ; $i <= 100 ; $i += 5) {
                    $baseFillingsArray[$i] = array('prices' => array());
                }
            }

            // loop through the printer products
            /** @var PrinterProduct $product */
            foreach ($printer->getProducts() as $product) {

                // ignore product if it is not available
                if (!$product->isAvailable()) {
                    continue;
                }

                $technology = $printer->getTechnology();
                $this->addTechnologyRef($technology);
                if (!array_key_exists($technology->getId(), $result)) {
                    $result[$technology->getId()] = array(
                        'materials' => array()
                    );
                }

                $materialsArray = &$result[$technology->getId()]['materials'];
                $material = $product->getMaterial();
                $this->addMaterialRef($material);
                if (!array_key_exists($material->getId(), $materialsArray)) {
                    $materialsArray[$material->getId()] = array(
                        'colors' => array()
                    );
                }

                $colorsArray = &$materialsArray[$material->getId()]['colors'];
                /** @var Color $color */
                foreach ($product->getColors() as $color) {

                    $this->addColorRef($color);
                    if (!array_key_exists($color->getId(), $colorsArray)) {
                        $colorsArray[$color->getId()] = array(
                            'layers' => array()
                        );
                    }

                    $layersArray = &$colorsArray[$color->getId()]['layers'];
                    $layer = $product->getLayer();
                    $this->addLayerRef($layer);
                    if (!array_key_exists($layer->getId(), $layersArray)) {
                        $layersArray[$layer->getId()] = array(
                            'fillings' => $baseFillingsArray
                        );
                    }
                    
                    $volumeMethode = $printer->getVolumeMethode();
                    if (Printer::VOLUME_METHODE_BOUNDING_BOX  == $volumeMethode) {
                        $vol = $volumeBoundingBox;
                    }else {
                        $vol = $volume;
                    }
                    

                    $fillingsArray = &$layersArray[$layer->getId()]['fillings'];
                    foreach ($fillingsArray as $fillingId => &$pricesArray) {
                        $basePrice = $product->getPrice100();// default price
                        if (FillingRate::NONE !== $fillingId) {
                            if (25 < $fillingId && 50 >= $fillingId) {
                                $basePrice = $product->getPrice50();
                            } elseif (25 >= $fillingId) {
                                $basePrice = $product->getPrice25();
                            }
                        }

                        $priceTaxExcl = $this->getPrice($vol, $basePrice, $product, $quantity, false);
                        $priceTaxIncl = $this->getPrice($vol, $basePrice, $product, $quantity, true);
                        if (!array_key_exists($maker->getId(), $pricesArray['prices'])) {
                            $pricesArray['prices'][$maker->getId()] = array(
                                'price_tax_excl' => $priceTaxExcl,
                                'price_tax_incl' => $priceTaxIncl,
                                'options'        => array()
                                // we could add a 'price_setup' key if we later need to return the setup price (as front JS already understands it)
                            );
                        } else {
                            // there was already that combination for that maker: compare the two prices
                            if ($priceTaxExcl < $pricesArray['prices'][$maker->getId()]['price_tax_excl']) {
                                // we found a lower price, so we need to reset the maker best price and related options
                                $pricesArray['prices'][$maker->getId()] = array(
                                    'price_tax_excl' => $priceTaxExcl,
                                    'price_tax_incl' => $priceTaxIncl,
                                    'options'        => array()
                                );
                            }
                        }

                        // finishing options (if any)
                        /** @var PrinterProductFinishing $productFinishing */
                        foreach ($product->getFinishings() as $productFinishing) {
                            $finishing = $productFinishing->getFinishing();
                            $this->addFinishingRef($finishing);
                            if (!array_key_exists($finishing->getId(), $pricesArray['prices'][$maker->getId()]['options'])) {
                                $optionPriceTaxExcl = $quantity * $productFinishing->getPrice();
                                $optionPriceTaxIncl = (int) round($optionPriceTaxExcl * (100.0 + $this->taxRate) / 100);
                                $pricesArray['prices'][$maker->getId()]['options'][$finishing->getId()]['option_tax_excl'] = $optionPriceTaxExcl;
                                $pricesArray['prices'][$maker->getId()]['options'][$finishing->getId()]['option_tax_incl'] = $optionPriceTaxIncl;
                            }
                        }

                        // Note: very important to unset as we pass $pricesArray as reference in the foreach loop
                        // @see warning in http://fr.php.net/manual/en/control-structures.foreach.php
                        unset($pricesArray);
                    }
                }
            }
        }

        $jsonArray = array(
            'combinations' => array(
                'technologies' => $result
            ),
            'makers' => $this->makers,
            'ref'    => $this->ref
        );

        return json_encode($jsonArray, JSON_FORCE_OBJECT);
    }

    /**
     * @param float           $volume object volume in mm3
     * @param int             $basePrice price per cm3, in cents
     * @param PrinterProduct  $product
     * @param int             $quantity
     * @param bool            $withTax
     * @return int            rounded price in cents
     */
    private function getPrice($volume, $basePrice, PrinterProduct $product, $quantity = 1, $withTax = true)
    {
        // setup price
        $setupPriceWithNoTax = $product->getPrinter()->getSetupPrice();

        // object price
        $objectPriceWithNoTax = $basePrice * ceil($volume / 1000);// base price is per cm3 and volume is in mm3, so divide by 1000 ; also round to the next int (UD20)

        // get tax coefficient
        $taxCoefficient = 1.0;
        if ($withTax) {
            $taxCoefficient = (100.0 + $this->taxRate) / 100;
        }

        // return rounded int
        return (int)round(($setupPriceWithNoTax + $quantity * $objectPriceWithNoTax) * $taxCoefficient);
    }

    /**
     * Add a technology to the ref array, if not already present
     *
     * @param Technology $technology
     */
    private function addTechnologyRef(Technology $technology)
    {
        if (!array_key_exists($technology->getId(), $this->ref['technology'])) {
            $this->ref['technology'][$technology->getId()] = array(
                'name'             => $technology->getName(),
                'has_filling_rate' => $technology->hasFillingRate()
            );
            if (null !== $technology->getDescription()) {
                $this->ref['technology'][$technology->getId()]['description'] = nl2br($technology->getDescription());
            }
            if (null !== $technology->getEditorialLink()) {
                $this->ref['technology'][$technology->getId()]['link'] = $technology->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$technology->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $technology->getDescription()) {
                    $this->ref['technology'][$technology->getId()]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['technology'][$technology->getId()]['description'] = $htmlLink;
                }
            }
            if (null !== $technology->getImageName()) {
                $this->ref['technology'][$technology->getId()]['image'] = '<img src="' . $this->liipImagineCacheManager->getBrowserPath($technology->getImageName(), 'ref_image') . '"><br>';
            }
        }
    }

    /**
     * Add a material to the ref array, if not already present
     *
     * @param Material $material
     */
    private function addMaterialRef(Material $material)
    {
        if (!array_key_exists($material->getId(), $this->ref['material'])) {
            $this->ref['material'][$material->getId()] = array(
                'name' => $material->getName()
            );
            if (null !== $material->getDescription()) {
                $this->ref['material'][$material->getId()]['description'] = nl2br($material->getDescription());
            }
            if (null !== $material->getEditorialLink()) {
                $this->ref['material'][$material->getId()]['link'] = $material->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$material->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $material->getDescription()) {
                    $this->ref['material'][$material->getId()]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['material'][$material->getId()]['description'] = $htmlLink;
                }
            }
            if (null !== $material->getImageName()) {
                $this->ref['material'][$material->getId()]['image'] = '<img src="' . $this->liipImagineCacheManager->getBrowserPath($material->getImageName(), 'ref_image') . '"><br>';
            }
        }
    }

    /**
     * Add a color to the ref array, if not already present
     *
     * @param Color $color
     */
    private function addColorRef(Color $color)
    {
        $hexadecimalCode = '#000000';
        if (null !== $color->getHexadecimalCode()) {
            $hexadecimalCode = $color->getHexadecimalCode();
        }
        if (!array_key_exists($color->getId(), $this->ref['color'])) {
            $this->ref['color'][$color->getId()] = array(
                'name' => $color->getName(),
                'code' => $hexadecimalCode
            );
            if (null !== $color->getDescription()) {
                $this->ref['color'][$color->getId()]['description'] = nl2br($color->getDescription());
            }
            if (null !== $color->getEditorialLink()) {
                $this->ref['color'][$color->getId()]['link'] = $color->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$color->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $color->getDescription()) {
                    $this->ref['color'][$color->getId()]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['color'][$color->getId()]['description'] = $htmlLink;
                }
            }
            if (null !== $color->getImageName()) {
                $this->ref['color'][$color->getId()]['image'] = '<img src="' . $this->liipImagineCacheManager->getBrowserPath($color->getImageName(), 'ref_image') . '"><br>';
            }
        }
    }

    /**
     * Add a layer to the ref array, if not already present
     *
     * @param Layer $layer
     */
    private function addLayerRef(Layer $layer)
    {
        if (!array_key_exists($layer->getId(), $this->ref['layer'])) {
            $this->ref['layer'][$layer->getId()] = array(
                'name' => $layer->getHeightWithUnit()
            );
            if (null !== $layer->getDescription()) {
                $this->ref['layer'][$layer->getId()]['description'] = nl2br($layer->getDescription());
            }
            if (null !== $layer->getEditorialLink()) {
                $this->ref['layer'][$layer->getId()]['link'] = $layer->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$layer->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $layer->getDescription()) {
                    $this->ref['layer'][$layer->getId()]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['layer'][$layer->getId()]['description'] = $htmlLink;
                }
            }
            if (null !== $layer->getImageName()) {
                $this->ref['layer'][$layer->getId()]['image'] = '<img src="' . $this->liipImagineCacheManager->getBrowserPath($layer->getImageName(), 'ref_image'). '"><br>';
            }
        }
    }

    /**
     * Add a finishing to the ref array, if not already present
     *
     * @param Finishing $finishing
     */
    private function addFinishingRef(Finishing $finishing)
    {
        if (!array_key_exists($finishing->getId(), $this->ref['finishing'])) {
            $this->ref['finishing'][$finishing->getId()] = array(
                'name' => $finishing->getName()
            );
            if (null !== $finishing->getDescription()) {
                $this->ref['finishing'][$finishing->getId()]['description'] = nl2br($finishing->getDescription());
            }
            if (null !== $finishing->getEditorialLink()) {
                $this->ref['finishing'][$finishing->getId()]['link'] = $finishing->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$finishing->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $finishing->getDescription()) {
                    $this->ref['finishing'][$finishing->getId()]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['finishing'][$finishing->getId()]['description'] = $htmlLink;
                }
            }
        }
    }
}