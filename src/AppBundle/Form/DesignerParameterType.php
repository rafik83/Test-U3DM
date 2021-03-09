<?php

namespace AppBundle\Form;

use AppBundle\Entity\Maker;
use AppBundle\Entity\ProjectType;
use AppBundle\Entity\Skill;
use AppBundle\Entity\Software;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignerParameterType extends AbstractType
{

    /**
     * DesignerParameterType constructor
     *
     */
    public function __construct()
    {

    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('designProjectTypes', EntityType::class, array(
                'required'     => true,
                'label'        => 'designer.form.field.project.type',
                'class'        => 'AppBundle\Entity\ProjectType',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = true');
                },
                'choice_label' => 'name',
                'expanded'     => true,
                'multiple'     => true,
                'choice_attr'  => function (ProjectType $projectType, $key, $index) {
                    return array(
                        'data-description' => $projectType->getDescriptionMaker()
                    );
                }
            ))
            ->add('designSkills', EntityType::class, array(
                'required'     => true,
                'label'        => 'designer.form.field.project.skills',
                'class'        => 'AppBundle\Entity\Skill',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = true');
                },
                'choice_label' => 'name',
                'expanded'     => true,
                'multiple'     => true,
                'choice_attr'  => function (Skill $skill, $key, $index) {
                    return array(
                        'data-description' => $skill->getDescription(),
                        'data-editorial'   => $skill->getEditorialLink()
                    );
                }
            ))
            ->add('designSoftwares', EntityType::class, array(
                'required'     => true,
                'label'        => 'designer.form.field.project.software',
                'class'        => 'AppBundle\Entity\Software',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = true');
                },
                'choice_label' => 'name',
                'expanded'     => true,
                'multiple'     => true,
                'choice_attr'  => function (Software $software, $key, $index) {
                    return array(
                        'data-description' => $software->getDescription(),
                        'data-editorial'   => $software->getEditorialLink()
                    );
                }
            ))
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Maker::class
        ));
    }

    public function getName()
    {
        return 'appbundle_designer_parameter';
    }
}