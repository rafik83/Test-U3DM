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


class SignalAdminType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('signalRef', EntityType::class, array(
                'required'     => true,
                'placeholder' => 'Selectionner la raison du signal',
                'label'        => 'Raison',
                'class'        => 'AppBundle\Entity\SignalRef',
                'choice_label' => 'signalName'
            ))
            ->add('description', TextareaType::class, array(
                'required' => true,
                'label'    => "Commentaire",
                'attr' => array(
                    'placeholder' => 'Saississez votre commentaire',
                    'rows'     => '2')
            ))
            
            
        ;
        if ($options['admin_user']) {

            $builder
                ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label' => 'Activer'

            ));

        } else {
            $builder
                ->add('save', SubmitType::class, [
                    'label' => 'Enregistrer',
                    'attr' => ['class' => 'btn btn-response']
                    ])
            ;
            
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Signal::class,
            'admin_user' => false
        ));
    }

    public function getName()
    {
        return 'appbundle_signal_admin';
    }
}