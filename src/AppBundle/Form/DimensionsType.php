<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DimensionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('x', NumberType::class, array(
                'label' => 'dimensions.form.field.x'
            ))
            ->add('y', NumberType::class, array(
                'label' => 'dimensions.form.field.y'
            ))
            ->add('z', NumberType::class, array(
                'label' => 'dimensions.form.field.z'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => 'AppBundle\Entity\Embeddable\Dimensions',
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_dimensions';
    }
}