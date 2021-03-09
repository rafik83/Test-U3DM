<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinishingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.finishing.form.field.enabled'
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'admin.finishing.form.field.name'
            ))
            ->add('nameLong', TextType::class, array(
                'required' => false,
                'label'    => 'admin.finishing.form.field.name_long'
            ))
            ->add('nameEnglish', TextType::class, array(
                'required' => false,
                'label'    => 'admin.finishing.form.field.name_english'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.finishing.form.field.description'
            ))
            ->add('editorialLink', TextType::class, array(
                'required' => false,
                'label'    => 'admin.finishing.form.field.editorial_link'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Finishing'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_finishing';
    }
}