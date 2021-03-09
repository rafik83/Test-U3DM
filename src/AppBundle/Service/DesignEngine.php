<?php

namespace AppBundle\Service;

use AppBundle\Entity\Skill;
use AppBundle\Entity\Software;
use AppBundle\Entity\ProjectType;
use AppBundle\Entity\Field;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Embeddable\Dimensions;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Service to search available designer combinations and prices
 */
class DesignEngine
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
            'skill'       => array(),
            'field'       => array(),
            'projectType' => array(),
            'software' => array()
        );

        // set tax rate
        $this->taxRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_TAX_RATE)->getValue();
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface    $translator
     * @param CacheManager           $liipImagineCacheManager
     * @return string JSON object of referentiel
     */
    public function getRef()
    {
        // result array
        $result = array();

        $skills = $this->entityManager->getRepository('AppBundle:Skill')->findBy(array('enabled' => true), array('name' => 'ASC'));
        foreach ($skills as $key => $skill) {

            $this->ref['skill'][$key] = array(
                'id' => $skill->getId(),
                'name' => $skill->getName(),
                'description' => $skill->getDescription()
            );

            if (null !== $skill->getEditorialLink()) {
                $this->ref['skill'][$key]['link'] = $skill->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$skill->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $skill->getDescription()) {
                    $this->ref['skill'][$key]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['skill'][$key]['description'] = $htmlLink;
                }
            } 
        }

        $softwares = $this->entityManager->getRepository('AppBundle:Software')->findBy(array('enabled' => true), array('name' => 'ASC'));
        foreach ($softwares as $key => $software) {

            $this->ref['software'][$key] = array(
                'id' => $software->getId(),
                'name' => $software->getName(),
                'description' => $software->getDescription()
            );

            if (null !== $software->getEditorialLink()) {
                $this->ref['software'][$key]['link'] = $software->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$software->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $software->getDescription()) {
                    $this->ref['software'][$key]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['software'][$key]['description'] = $htmlLink;
                }
            }
        }

        $fields = $this->entityManager->getRepository('AppBundle:Field')->findBy(array('enabled' => true), array('name' => 'ASC'));
        foreach ($fields as $key => $field) {

            $this->ref['field'][$key] = array(
                'id' => $field->getId(),
                'name' => $field->getName(),
                'description' => $field->getDescription()
            );


            if (null !== $field->getEditorialLink()) {
                $this->ref['field'][$key]['link'] = $field->getEditorialLink();
                // add link to description (makes front job easier)
                $htmlLink = '<a href="'.$field->getEditorialLink().'" target="_blank">+ Plus d\'infos</a>';
                if (null !== $field->getDescription()) {
                    $this->ref['field'][$key]['description'] .= '<br>' . $htmlLink;
                } else {
                    $this->ref['field'][$key]['description'] = $htmlLink;
                }
            }


        }

        $projectTypes = $this->entityManager->getRepository('AppBundle:ProjectType')->findBy(array('enabled' => true,'tagSpec' => null), array('name' => 'ASC'));
        foreach ($projectTypes as $key => $projectType) {

            $this->ref['projectType'][$key] = array(
                'id' => $projectType->getId(),
                'name' => $projectType->getName(),
                'scanner' => $projectType->isScanner(),
                'description' => $projectType->getDescription(),
                'tag' => "",
                'addressProject' => $projectType->isAddressProject(),
                'addressProjectLabel' => $projectType->getAddressProjectLabel(),
                'shippingChoice'=> $projectType->isShippingChoice()
            );
        }

        $projectTypeSpecs = $this->entityManager->getRepository('AppBundle:ProjectType')->findBy(array('enabled' => true,'tagSpec' => 'COVID'), array('name' => 'ASC'));
        foreach ($projectTypeSpecs as $key => $projectTypeSpec) {

            $this->ref['projectTypeSpec'][$key] = array(
                'id' => $projectTypeSpec->getId(),
                'name' => $projectTypeSpec->getName(),
                'scanner' => $projectTypeSpec->isScanner(),
                'description' => $projectTypeSpec->getDescription(),
                'tag' => "COVID",
                'addressProject' => $projectTypeSpec->isAddressProject(),
                'addressProjectLabel' => $projectTypeSpec->getAddressProjectLabel(),
                'shippingChoice'=> $projectType->isShippingChoice()


            );
        }
        $jsonArray = array(
            'ref'    => $this->ref
        );

        return json_encode($jsonArray, JSON_FORCE_OBJECT);
    }

}