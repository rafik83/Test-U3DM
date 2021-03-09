<?php

namespace AppBundle\Form;

use AppBundle\Entity\ModelComments;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Vich\UploaderBundle\Form\Type\VichFileType;


class ModelSignalType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('signalRef', EntityType::class, array(
                'required'     => true,
                'placeholder' => 'Choisissez une licence',
                'label'        => 'model.form.label.licences',
                'class'        => 'AppBundle\Entity\ModelLicense',
                'choice_label' => 'name',
            ))
            ->add('description', TextareaType::class, array(
                'required' => true,
                'label'    => "Commentaire",
                'attr' => array(
                    'placeholder' => 'Saississez votre commentaire')
            ))
            
            
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Signal::class
        ));
    }

    public function getName()
    {
        return 'appbundle_model_signal';
    }
}