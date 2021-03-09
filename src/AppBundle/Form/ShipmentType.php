<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShipmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parcelNumber', TextType::class, array(
                'required' => true,
                'label'    => 'Ref',
                'attr' => array('placeholder' => 'Reférence colis')
                
            ))
            ->add('trackingMakerUrl', TextType::class, array(
                'label' => 'Url',
                'required' => true,
                'attr' => array('placeholder' => 'URL suivi transporteur (http://)')
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Shipment',
            'admin_user' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_shipment';
    }
}