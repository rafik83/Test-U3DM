<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class QuotationLineType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if($options['user_type'] == 'admin'){

            $disabled = true;
        } else {

            $disabled = false;
        }

        if($options['view_only'] == true){

            $disabled = true;
        } else {

            $disabled = false;
        }

        $builder
            ->add('description', TextType::class, array(
                'required' => true,
                'label'    => false,
                'disabled' => $disabled
            ))
            ->add('quantity', NumberType::class, array(
                'required' => true,
                'attr' => array('class' => 'quantity type-field','readonly' => $disabled, 'min' => 0,"type" => "number"),
                'label'    => false,
            ))
            ->add('price', MoneyType::class, array(
                'required' => true,
                'attr' => array('class' => 'pu-ht type-field','readonly' => $disabled),
                'divisor'  => 100,
                'currency' => false,
                'label'    => false,
            ))
            ->add('number', NumberType::class, array(
                'required' => true,
                'label'    => false,
                'attr' => array('class' => 'index-line','readonly' => true, "tabindex" => "-1")
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\QuotationLine',
            'view_only' => false,
            'user_type' => 'maker'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_quotation_line';
    }
}