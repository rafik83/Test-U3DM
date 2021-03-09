<?php

namespace AppBundle\Form;

use AppBundle\Entity\Prospect;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProspectCustomerType extends ProspectType
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

        // Customer Prospect specific fields
        $builder
            ->add('customer_type', ChoiceType::class, array(
                'required'   => true,
                'label_attr' => array('class' => 'no-required-display'),
                'label'      => false,
                'expanded'   => true,
                'choices'    => array(
                    'prospect.form.field.customer_type.company.label'    => Prospect::CUSTOMER_TYPE_COMPANY,
                    'prospect.form.field.customer_type.individual.label' => Prospect::CUSTOMER_TYPE_INDIVIDUAL
                )
            ))
        ;

        // default the customer type to Company
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $event->getData()->setCustomerType(Prospect::CUSTOMER_TYPE_COMPANY);
            }
        );

        // upon submission, set the Prospect as not a Maker
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $event->getData()->setMaker(false);
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
        return 'appbundle_prospect_customer';
    }
}