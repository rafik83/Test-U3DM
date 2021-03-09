<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class LayerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.layer.form.field.enabled'
            ))
            ->add('height', IntegerType::class, array(
                'required' => true,
                'label'    => 'admin.layer.form.field.name'
            ))
            ->add('definition', TextType::class, array(
                'required' => false,
                'label'    => 'admin.layer.form.field.definition'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.layer.form.field.description'
            ))
            ->add('imageFile', VichImageType::class, array(
                'required'        => false,
                'label'           => 'Image',
                'allow_delete'    => true,
                'delete_label'    => 'Supprimer',
                'download_uri'    => false,
                'imagine_pattern' => 'ref_image'
            ))
            ->add('editorialLink', TextType::class, array(
                'required' => false,
                'label'    => 'admin.layer.form.field.editorial_link'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Layer'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_layer';
    }
}