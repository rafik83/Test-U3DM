<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectTypeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.enabled'
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'admin.project_type.form.field.name'
            ))
            ->add('tagSpec', TextType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.tagSpec'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.description'
            ))
            ->add('descriptionMaker', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.description_maker'
            ))            
            ->add('scanner', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.scanner'
            ))
            ->add('addressProject', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.addressProject'
            ))
            ->add('addressProjectLabel', TextType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.addressProjectLabel'
            ))

            ->add('shippingChoice', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.shippingChoice'
            ))
            ->add('shipping', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.shipping'
            ))
            ->add('file', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.project_type.form.field.file'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProjectType'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ref_project_type';
    }
}