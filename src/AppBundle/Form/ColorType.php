<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ColorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.color.form.field.enabled'
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'admin.color.form.field.name'
            ))
            ->add('nameLong', TextType::class, array(
                'required' => false,
                'label'    => 'admin.color.form.field.name_long'
            ))
            ->add('nameEnglish', TextType::class, array(
                'required' => false,
                'label'    => 'admin.color.form.field.name_english'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.color.form.field.description'
            ))
            ->add('imageFile', VichImageType::class, array(
                'required'        => false,
                'label'           => 'Image',
                'allow_delete'    => true,
                'delete_label'    => 'Supprimer',
                'download_uri'    => false,
                'imagine_pattern' => 'ref_image'
            ))
            ->add('hexadecimalCode', TextType::class, array(
                'required' => true,
                'label'    => 'admin.color.form.field.hexadecimal_code'
            ))
            ->add('editorialLink', TextType::class, array(
                'required' => false,
                'label'    => 'admin.color.form.field.editorial_link'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Color'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_color';
    }
}