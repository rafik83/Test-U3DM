<?php

namespace AppBundle\Form;

use AppBundle\Entity\Model;
use AppBundle\Entity\ModelLicense;
use AppBundle\Entity\Signal;
use AppBundle\Repository\ModelStatusRepository;
use AppBundle\EventListener\ModelListener;
use AppBundle\EventListener\SuggestionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\TranslatorInterface;


class SignalType extends AbstractType
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
                'label'    => "model.form.label.description",
                'attr' => array(
                    'placeholder' => 'Décrivez au mieux pourquoi vous signalez ce modèle',
                    'rows'     => '4')
            ))
            ->add('email', EmailType::class, array(
                'required' => false,
                'label'    => 'user.form.field.email'
            ));
            //->add('save', SubmitType::class, ['label' => 'model.form.label.save'])

            if ($options['admin_user']) {

                $builder
                    ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Activer'
    
                ));
    
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
        return 'appbundle_add_signal';
    }
}