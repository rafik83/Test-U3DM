<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrecisionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.precision.form.field.enabled'
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'admin.precision.form.field.name'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.precision.form.field.description'
            ))
            ->add('editorialLink', TextType::class, array(
                'required' => false,
                'label'    => 'admin.precision.form.field.editorial_link'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Precision'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ref_precision';
    }
}