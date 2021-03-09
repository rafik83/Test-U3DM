<?php

namespace AppBundle\Form;

use AppBundle\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use AppBundle\Form\AddressType;
use AppBundle\Form\DimensionsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'admin.project.form.field.name.label',
                'required' => true,
                'disabled' => $options['disabled']
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'admin.coupon.form.field.description.label',
                'required' => true,
                'attr' => array('rows' => 10),
                'disabled' => $options['disabled']
            ))
            ->add('type', EntityType::class, array(
                'required'     => true,
                'label'        => 'admin.project.form.field.type.label',
                'class'        => 'AppBundle\Entity\ProjectType',
                'choice_label' => 'name',
                'disabled' => $options['disabled']
            ))
            ->add('fields', EntityType::class, array(
                'required'     => false,
                'label'        => 'admin.project.form.field.fields.label',
                'class'        => 'AppBundle\Entity\Field',
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => true,
                'disabled' => $options['disabled']
            ))
            ->add('deliveryTime', ChoiceType::class, array(
                'label' => 'admin.project.form.field.delivery.time.label',
                'required' => true,
                'choices' => array(
                    '1 semaine' => Project::DELIVERY_ONE_WEEK,
                    '15 jours' => Project::DELIVERY_FIFTEEN_DAYS,
                    '1 mois' => Project::DELIVERY_ONE_MONTH,
                    '3 mois' => Project::DELIVERY_THREE_MONTHS,
                    '>3 mois' => Project::DELIVERY_MORE_THAN_THREE_MONTHS
                )
                /*'disabled' => true*/
            ))
            ->add('scanAddress', AddressType::class, array(
                'label' => 'admin.project.form.field.address.scanner.label',
                'required' => false,
                'attr' => array('class' => 'scanner-required')
            ))
            ->add('dimensions', DimensionsType::class, array(
                'label' => 'admin.project.form.field.dimensions.label',
                'required' => false,
                'attr' => array('class' => 'scanner-required')
            ))
            /*->add('scanOnSite', CheckboxType::class, array(
                'label' => 'admin.project.form.field.scan.on.site.label',
                'required' => false,
                'attr' => array('class' => 'scanner-required')
            ))*/
            ->add('softwares', EntityType::class, array(
                'required'     => false,
                'label'        => 'admin.project.form.field.softwares.label',
                'class'        => 'AppBundle\Entity\Software',
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => true,
                'disabled' => $options['disabled']
            ))
            ->add('skills', EntityType::class, array(
                'required'     => false,
                'label'        => 'admin.project.form.field.skills.label',
                'class'        => 'AppBundle\Entity\Skill',
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => true,
                'disabled' => $options['disabled']
            ))
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Project',
            'disabled' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_project';
    }
}