<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProspectMakerType extends ProspectType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Common fields to all Prospects (but required fields may differ)
        $builder
            ->add('email', EmailType::class, array(
                'required' => true,
                'label'    => false,
                'attr'     => array(
                    'placeholder' => 'prospect.form.field.email.label'
                )
            ))
            ->add('firstname', TextType::class, array(
                'required' => true,
                'label'    => false,
                'attr'     => array(
                    'placeholder' => 'prospect.form.field.firstname.label'
                )
            ))
            ->add('lastname', TextType::class, array(
                'required' => true,
                'label'    => false,
                'attr'     => array(
                    'placeholder' => 'prospect.form.field.lastname.label'
                )
            ))
        ;

        // Maker Prospect specific fields
        $builder
            ->add('printer', CheckboxType::class, array(
                'required' => false,
                'label'    => 'prospect.form.field.maker.printer.label'
            ))
            ->add('designer', CheckboxType::class, array(
                'required' => false,
                'label'    => 'prospect.form.field.maker.designer.label'
            ))
        ;

        // upon submission, set the Prospect as a Maker
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $event->getData()->setMaker(true);
            }
        );

        // add a subscriber to handle the form properly
        $builder->addEventSubscriber($this->listener);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_prospect_maker';
    }
}