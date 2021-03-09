<?php

namespace AppBundle\Form;

use AppBundle\Entity\PrinterProductFinishing;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrinterProductFinishingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('finishing', EntityType::class, array(
                'required'     => true,
                'label'        => 'printer_product_finishing.form.field.finishing',
                'class'        => 'AppBundle\Entity\Finishing',
                'choice_label' => 'name'
            ))
            ->add('price', MoneyType::class, array(
                'required' => true,
                'label'    => 'printer_product_finishing.form.field.price',
                'divisor'  => 100,
                'currency' => 'eur'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PrinterProductFinishing::class,
        ));
    }

    public function getName()
    {
        return 'app_printer_product_finishing';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}