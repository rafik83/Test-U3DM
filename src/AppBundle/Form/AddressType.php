<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (false === $options['hide_firstname']) {
            $builder
                ->add('firstname', TextType::class, array(
                    'label' => 'address.form.field.firstname',
                    'required' => true,
                    'label_attr' => array('class' => 'required') // useful when having a non required address in a form
                ));
        }
        if (false === $options['hide_lastname']) {
            $builder
                ->add('lastname', TextType::class, array(
                    'label' => 'address.form.field.lastname',
                    'required' => true,
                    'label_attr' => array('class' => 'required')
                ));
        }
        if (false === $options['hide_company']) {
            $companyLabelAttr = array();
            if (true === $options['company_required']) {
                $companyLabelAttr = array('class' => 'required');
            }
            $builder
                ->add('company', TextType::class, array(
                    'label'    => $options['company_label'],
                    'required' => $options['company_required'],
                    'attr'     => array('class' => 'company-only'),
                    'label_attr' => $companyLabelAttr
                ));
        }
        $builder
            ->add('street1', TextType::class, array(
                'label'    => 'address.form.field.street1',
                'required' => true,
                'label_attr' => array('class' => 'required')
            ))
            ->add('street2', TextType::class, array(
                'label'    => 'address.form.field.street2',
                'required' => false
            ))
            ->add('zipcode', TextType::class, array(
                'label'    => 'address.form.field.zip_code',
                'required' => true,
                'label_attr' => array('class' => 'required')
            ))
            ->add('city', TextType::class, array(
                'label'    => 'address.form.field.city',
                'required' => true,
                'label_attr' => array('class' => 'required')
            ))
            ->add('country', CountryType::class, array(
                'label'             => 'address.form.field.country',
                'required'          => true,
                'placeholder'       => '-',
                'preferred_choices' => array('FR'),
                'label_attr' => array('class' => 'required')
            ))
            ->add('telephone', TextType::class, array(
                'label'    => 'address.form.field.telephone',
                'required' => true,
                'label_attr' => array('class' => 'required')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'     => 'AppBundle\Entity\Address',
            'hide_firstname' => false,
            'hide_lastname'  => false,
            'hide_company'   => false,
            'company_label'    => 'Société',
            'company_required' => false
        ));
    }

    public function getName()
    {
        return 'appbundle_address';
    }
}