<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Color;
use AppBundle\Entity\Field;
use AppBundle\Entity\Finishing;
use AppBundle\Entity\Layer;
use AppBundle\Entity\Material;
use AppBundle\Entity\Precision;
use AppBundle\Entity\ProjectType;
use AppBundle\Entity\Resolution;
use AppBundle\Entity\Skill;
use AppBundle\Entity\Software;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Technology;
use AppBundle\Entity\TechnologyScanner;
use AppBundle\Form\ColorType;
use AppBundle\Form\FieldType;
use AppBundle\Form\FinishingType;
use AppBundle\Form\LayerType;
use AppBundle\Form\MaterialType;
use AppBundle\Form\PrecisionType;
use AppBundle\Form\ProjectTypeType;
use AppBundle\Form\ResolutionType;
use AppBundle\Form\SkillType;
use AppBundle\Form\SoftwareType;
use AppBundle\Form\TagType;
use AppBundle\Form\TechnologyScannerType;
use AppBundle\Form\TechnologyType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/%app.admin_directory%/ref")
 */
class AdminRefController extends Controller
{
    /**
     * @Route("/color/list", name="admin_color_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function colorListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/color/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Color')->findAll()));
    }

    /**
     * @Route("/color/add", name="admin_color_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function colorAddAction(Request $request, ObjectManager $entityManager)
    {
        $color = new Color();
        $form = $this->createForm(ColorType::class, $color);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($color);
            $entityManager->flush();
            $this->addFlash('success', 'admin.color.flash.create');
            return $this->redirectToRoute('admin_color_list');
        }
        return $this->render('admin/color/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/color/{id}/edit", requirements={"id" = "\d+"}, name="admin_color_edit")
     *
     * @param Color $color
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function colorEditAction(Color $color, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(ColorType::class, $color);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.color.flash.update');
            return $this->redirectToRoute('admin_color_list');
        }
        return $this->render('admin/color/form.html.twig', array('form' => $form->createView(), 'color' => $color));
    }

    /**
     * @Route("/finishing/list", name="admin_finishing_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function finishingListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/finishing/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Finishing')->findAll()));
    }

    /**
     * @Route("/finishing/add", name="admin_finishing_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function finishingAddAction(Request $request, ObjectManager $entityManager)
    {
        $finishing = new Finishing();
        $form = $this->createForm(FinishingType::class, $finishing);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($finishing);
            $entityManager->flush();
            $this->addFlash('success', 'admin.finishing.flash.create');
            return $this->redirectToRoute('admin_finishing_list');
        }
        return $this->render('admin/finishing/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/finishing/{id}/edit", requirements={"id" = "\d+"}, name="admin_finishing_edit")
     *
     * @param Finishing $finishing
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function finishingEditAction(Finishing $finishing, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(FinishingType::class, $finishing);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.finishing.flash.update');
            return $this->redirectToRoute('admin_finishing_list');
        }
        return $this->render('admin/finishing/form.html.twig', array('form' => $form->createView(), 'finishing' => $finishing));
    }

    /**
     * @Route("/layer/list", name="admin_layer_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function layerListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/layer/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Layer')->findAll()));
    }

    /**
     * @Route("/layer/add", name="admin_layer_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function layerAddAction(Request $request, ObjectManager $entityManager)
    {
        $layer = new Layer();
        $form = $this->createForm(LayerType::class, $layer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($layer);
            $entityManager->flush();
            $this->addFlash('success', 'admin.layer.flash.create');
            return $this->redirectToRoute('admin_layer_list');
        }
        return $this->render('admin/layer/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/layer/{id}/edit", requirements={"id" = "\d+"}, name="admin_layer_edit")
     *
     * @param Layer $layer
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function layerEditAction(Layer $layer, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(LayerType::class, $layer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.layer.flash.update');
            return $this->redirectToRoute('admin_layer_list');
        }
        return $this->render('admin/layer/form.html.twig', array('form' => $form->createView(), 'layer' => $layer));
    }

    /**
     * @Route("/material/list", name="admin_material_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function materialListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/material/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Material')->findAll()));
    }

    /**
     * @Route("/material/add", name="admin_material_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function materialAddAction(Request $request, ObjectManager $entityManager)
    {
        $material = new Material();
        $form = $this->createForm(MaterialType::class, $material);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($material);
            $entityManager->flush();
            $this->addFlash('success', 'admin.material.flash.create');
            return $this->redirectToRoute('admin_material_list');
        }
        return $this->render('admin/material/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/material/{id}/edit", requirements={"id" = "\d+"}, name="admin_material_edit")
     *
     * @param Material $material
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function materialEditAction(Material $material, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(MaterialType::class, $material);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.material.flash.update');
            return $this->redirectToRoute('admin_material_list');
        }
        return $this->render('admin/material/form.html.twig', array('form' => $form->createView(), 'material' => $material));
    }

    /**
     * @Route("/tag/list", name="admin_tag_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function tagListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/tag/list.html.twig', array('tags' => $entityManager->getRepository('AppBundle:Tag')->findAll()));
    }

    /**
     * @Route("/tag/add", name="admin_tag_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function tagAddAction(Request $request, ObjectManager $entityManager)
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tag);
            $entityManager->flush();
            $this->addFlash('success', 'admin.tag.flash.create');
            return $this->redirectToRoute('admin_tag_list');
        }
        return $this->render('admin/tag/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/tag/{id}/edit", requirements={"id" = "\d+"}, name="admin_tag_edit")
     *
     * @param Tag $tag
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function tagEditAction(Tag $tag, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.tag.flash.update');
            return $this->redirectToRoute('admin_tag_list');
        }
        return $this->render('admin/tag/form.html.twig', array('form' => $form->createView(), 'tag' => $tag));
    }

    /**
     * @Route("/tag/{id}/remove", requirements={"id" = "\d+"}, name="admin_tag_remove")
     *
     * @param Tag $tag
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function tagRemoveAction(Tag $tag, ObjectManager $entityManager)
    {
        $entityManager->remove($tag);
        $entityManager->flush();
        $this->addFlash('success', 'admin.tag.flash.remove');
        return $this->redirectToRoute('admin_tag_list');
    }

    /**
     * @Route("/technology/list", name="admin_technology_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function technologyListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/technology/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Technology')->findAll()));
    }

    /**
     * @Route("/technology/add", name="admin_technology_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function technologyAddAction(Request $request, ObjectManager $entityManager)
    {
        $technology = new Technology();
        $form = $this->createForm(TechnologyType::class, $technology);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($technology);
            $entityManager->flush();
            $this->addFlash('success', 'admin.technology.flash.create');
            return $this->redirectToRoute('admin_technology_list');
        }
        return $this->render('admin/technology/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/technology/{id}/edit", requirements={"id" = "\d+"}, name="admin_technology_edit")
     *
     * @param Technology $technology
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function technologyEditAction(Technology $technology, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(TechnologyType::class, $technology);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.technology.flash.update');
            return $this->redirectToRoute('admin_technology_list');
        }
        return $this->render('admin/technology/form.html.twig', array('form' => $form->createView(), 'technology' => $technology));
    }

    /**
     * @Route("/project_type/list", name="admin_project_type_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function projectTypeListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/project_type/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:ProjectType')->findAll()));
    }

    /**
     * @Route("/project_type/add", name="admin_project_type_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function projectTypeAddAction(Request $request, ObjectManager $entityManager)
    {
        $projectType = new ProjectType();
        $form = $this->createForm(ProjectTypeType::class, $projectType);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectType);
            $entityManager->flush();
            $this->addFlash('success', 'admin.project_type.flash.create');
            return $this->redirectToRoute('admin_project_type_list');
        }
        return $this->render('admin/project_type/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/project_type/{id}/edit", requirements={"id" = "\d+"}, name="admin_project_type_edit")
     *
     * @param ProjectType $projectType
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function projectTypeEditAction(ProjectType $projectType, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(ProjectTypeType::class, $projectType);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.project_type.flash.update');
            return $this->redirectToRoute('admin_project_type_list');
        }
        return $this->render('admin/project_type/form.html.twig', array('form' => $form->createView(), 'project_type' => $projectType));
    }

    /**
     * @Route("/field/list", name="admin_field_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function fieldListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/field/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Field')->findAll()));
    }

    /**
     * @Route("/field/add", name="admin_field_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function fieldAddAction(Request $request, ObjectManager $entityManager)
    {
        $field = new Field();
        $form = $this->createForm(FieldType::class, $field);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($field);
            $entityManager->flush();
            $this->addFlash('success', 'admin.field.flash.create');
            return $this->redirectToRoute('admin_field_list');
        }
        return $this->render('admin/field/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/field/{id}/edit", requirements={"id" = "\d+"}, name="admin_field_edit")
     *
     * @param Field $field
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function fieldEditAction(Field $field, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(FieldType::class, $field);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.field.flash.update');
            return $this->redirectToRoute('admin_field_list');
        }
        return $this->render('admin/field/form.html.twig', array('form' => $form->createView(), 'field' => $field));
    }

    /**
     * @Route("/skill/list", name="admin_skill_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function skillListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/skill/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Skill')->findAll()));
    }

    /**
     * @Route("/skill/add", name="admin_skill_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function skillAddAction(Request $request, ObjectManager $entityManager)
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($skill);
            $entityManager->flush();
            $this->addFlash('success', 'admin.skill.flash.create');
            return $this->redirectToRoute('admin_skill_list');
        }
        return $this->render('admin/skill/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/skill/{id}/edit", requirements={"id" = "\d+"}, name="admin_skill_edit")
     *
     * @param Skill $skill
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function skillEditAction(Skill $skill, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.skill.flash.update');
            return $this->redirectToRoute('admin_skill_list');
        }
        return $this->render('admin/skill/form.html.twig', array('form' => $form->createView(), 'skill' => $skill));
    }

    /**
     * @Route("/software/list", name="admin_software_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function softwareListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/software/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Software')->findAll()));
    }

    /**
     * @Route("/software/add", name="admin_software_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function softwareAddAction(Request $request, ObjectManager $entityManager)
    {
        $software = new Software();
        $form = $this->createForm(SoftwareType::class, $software);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($software);
            $entityManager->flush();
            $this->addFlash('success', 'admin.software.flash.create');
            return $this->redirectToRoute('admin_software_list');
        }
        return $this->render('admin/software/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/software/{id}/edit", requirements={"id" = "\d+"}, name="admin_software_edit")
     *
     * @param Software $software
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function softwareEditAction(Software $software, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(SoftwareType::class, $software);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.software.flash.update');
            return $this->redirectToRoute('admin_software_list');
        }
        return $this->render('admin/software/form.html.twig', array('form' => $form->createView(), 'software' => $software));
    }

    /**
     * @Route("/precision/list", name="admin_precision_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function precisionListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/precision/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Precision')->findAll()));
    }

    /**
     * @Route("/precision/add", name="admin_precision_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function precisionAddAction(Request $request, ObjectManager $entityManager)
    {
        $precision = new Precision();
        $form = $this->createForm(PrecisionType::class, $precision);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($precision);
            $entityManager->flush();
            $this->addFlash('success', 'admin.precision.flash.create');
            return $this->redirectToRoute('admin_precision_list');
        }
        return $this->render('admin/precision/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/precision/{id}/edit", requirements={"id" = "\d+"}, name="admin_precision_edit")
     *
     * @param Precision $precision
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function precisionEditAction(Precision $precision, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(PrecisionType::class, $precision);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.precision.flash.update');
            return $this->redirectToRoute('admin_precision_list');
        }
        return $this->render('admin/precision/form.html.twig', array('form' => $form->createView(), 'precision' => $precision));
    }

    /**
     * @Route("/resolution/list", name="admin_resolution_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function resolutionListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/resolution/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:Resolution')->findAll()));
    }

    /**
     * @Route("/resolution/add", name="admin_resolution_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function resolutionAddAction(Request $request, ObjectManager $entityManager)
    {
        $resolution = new Resolution();
        $form = $this->createForm(ResolutionType::class, $resolution);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($resolution);
            $entityManager->flush();
            $this->addFlash('success', 'admin.resolution.flash.create');
            return $this->redirectToRoute('admin_resolution_list');
        }
        return $this->render('admin/resolution/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/resolution/{id}/edit", requirements={"id" = "\d+"}, name="admin_resolution_edit")
     *
     * @param Resolution $resolution
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function resolutionEditAction(Resolution $resolution, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(ResolutionType::class, $resolution);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.resolution.flash.update');
            return $this->redirectToRoute('admin_resolution_list');
        }
        return $this->render('admin/resolution/form.html.twig', array('form' => $form->createView(), 'resolution' => $resolution));
    }

    /**
     * @Route("/technology_scanner/list", name="admin_technology_scanner_list")
     *
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function technologyScannerListAction(ObjectManager $entityManager)
    {
        return $this->render('admin/technology_scanner/list.html.twig', array('refs' => $entityManager->getRepository('AppBundle:TechnologyScanner')->findAll()));
    }

    /**
     * @Route("/technology_scanner/add", name="admin_technology_scanner_add")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function technologyScannerAddAction(Request $request, ObjectManager $entityManager)
    {
        $technologyScanner = new TechnologyScanner();
        $form = $this->createForm(TechnologyScannerType::class, $technologyScanner);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($technologyScanner);
            $entityManager->flush();
            $this->addFlash('success', 'admin.technology_scanner.flash.create');
            return $this->redirectToRoute('admin_technology_scanner_list');
        }
        return $this->render('admin/technology_scanner/form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/technology_scanner/{id}/edit", requirements={"id" = "\d+"}, name="admin_technology_scanner_edit")
     *
     * @param TechnologyScanner $technologyScanner
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return Response
     */
    public function technologyScannerEditAction(TechnologyScanner $technologyScanner, Request $request, ObjectManager $entityManager)
    {
        $form = $this->createForm(TechnologyScannerType::class, $technologyScanner);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'admin.technology_scanner.flash.update');
            return $this->redirectToRoute('admin_technology_scanner_list');
        }
        return $this->render('admin/technology_scanner/form.html.twig', array('form' => $form->createView(), 'technology_scanner' => $technologyScanner));
    }
}