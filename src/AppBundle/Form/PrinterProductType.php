<?php

namespace AppBundle\Form;

use AppBundle\Entity\PrinterProduct;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class PrinterProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('available', CheckboxType::class, array(
                'required' => false,
                'label'    => 'printer_product.form.field.available'
            ))
            ->add('material', EntityType::class, array(
                'required'     => true,
                'label'        => 'printer_product.form.field.material',
                'class'        => 'AppBundle\Entity\Material',
                'choice_label' => 'name',
                'choices'      => $options['materials']
            ))
            ->add('layer', EntityType::class, array(
                'required'     => true,
                'label'        => 'printer_product.form.field.layer',
                'class'        => 'AppBundle\Entity\Layer',
                'choice_label' => 'height_with_unit'
            ))
            ->add('colors', Select2EntityType::class, array(
                'required'      => true,
                'label_attr'    => array('class' => 'required'),
                'label'         => 'printer_product.form.field.colors',
                'multiple'      => true,
                'remote_route'  => 'color_select2entity_ajax_list',
                'class'         => 'AppBundle\Entity\Color',
                'primary_key'   => 'id',
                'text_property' => 'name',
                'allow_clear'   => false,
                'delay'         => 100,
                'cache'         => true,
                'cache_timeout' => 60000,
                'language'      => 'fr',
                'placeholder'   => '',
                'minimum_input_length' => 2
            ))
            ->add('finishings', CollectionType::class, array(
                'required'      => true,
                'label'         => 'printer_product.form.field.finishings',
                'entry_type'    => PrinterProductFinishingType::class,
                'entry_options' => array('label' => false),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array('class' => 'printer-product-finishings-collection'),
                'prototype_name' => 'finishings_prototype'
            ));

        // add price fields depending if the technology allows filling rates or not
        if ($options['filling_rate']) {

            $builder
                ->add('price25', MoneyType::class, array(
                    'required' => true,
                    'label'    => 'printer_product.form.field.price_25',
                    'divisor'  => 100,
                    'currency' => 'eur',
                    'attr'     => array('unit' => 'printer_product.form.field.price.unit')
                ))
                ->add('price50', MoneyType::class, array(
                    'required' => true,
                    'label'    => 'printer_product.form.field.price_50',
                    'divisor'  => 100,
                    'currency' => 'eur',
                    'attr'     => array('unit' => 'printer_product.form.field.price.unit')
                ))
                ->add('price100', MoneyType::class, array(
                    'required' => true,
                    'label'    => 'printer_product.form.field.price_100',
                    'divisor'  => 100,
                    'currency' => 'eur',
                    'attr'     => array('unit' => 'printer_product.form.field.price.unit')
                ));

        } else {

            $builder
                ->add('price100', MoneyType::class, array(
                    'required' => true,
                    'label'    => 'printer_product.form.field.price_default',
                    'divisor'  => 100,
                    'currency' => 'eur',
                    'attr'     => array('unit' => 'printer_product.form.field.price.unit')
                ));

        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PrinterProduct::class
        ));

        $resolver->setRequired(array(
            'materials',
            'filling_rate'
        ));
    }

    public function getName()
    {
        return 'appbundle_printer_product';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}