<?php

namespace AppBundle\Form;

use AppBundle\Entity\Technology;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MaterialType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.material.form.field.enabled'
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'admin.material.form.field.name'
            ))
            ->add('nameLong', TextType::class, array(
                'required' => false,
                'label'    => 'admin.material.form.field.name_long'
            ))
            ->add('nameEnglish', TextType::class, array(
                'required' => false,
                'label'    => 'admin.material.form.field.name_english'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'admin.material.form.field.description'
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
                'label'    => 'admin.material.form.field.editorial_link'
            ))
            ->add('technologies', EntityType::class, array(
                'required'      => true,
                'label'         => 'admin.material.form.field.technologies',
                'class'         => Technology::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
                },
                'choice_label'  => 'name',
                'expanded'      => true,
                'multiple'      => true
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Material'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_material';
    }
}