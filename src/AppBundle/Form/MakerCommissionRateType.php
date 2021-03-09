<?php

namespace AppBundle\Form;

use AppBundle\Entity\Maker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MakerCommissionRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customCommissionRate', NumberType::class, array(
                'required' => false,
                'label'    => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Maker::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_maker_commission_rate';
    }
}