<?php

namespace AppBundle\Form;

use AppBundle\Entity\Printer;
use AppBundle\EventListener\PrinterListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrinterType extends AbstractType
{
    /**
     * @var PrinterListener
     */
    private $listener;

    /**
     * PrinterType constructor
     *
     * @param PrinterListener $listener
     */
    public function __construct(PrinterListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('available', CheckboxType::class, array(
                'required' => false,
                'label'    => 'printer.form.field.available'
            ))
            ->add('technology', EntityType::class, array(
                'required'     => true,
                'label'        => 'printer.form.field.technology',
                'class'        => 'AppBundle\Entity\Technology',
                'choice_label' => 'name',
                'expanded'     => true
            ))
            ->add('model', TextType::class, array(
                'required' => true,
                'label'    => 'printer.form.field.model'
            ))

            ->add('minVolume', NumberType::class, array(
                'required' => true,
                'label'    => 'printer.form.field.min_volume'
            ))
            ->add('maxDimensions', DimensionsType::class, array(
                'required' => true,
                'label'    => 'printer.form.field.max_dimensions'
            ))
            ->add('setupPrice', MoneyType::class, array(
                'required' => true,
                'label'    => 'printer.form.field.setup_price',
                'divisor'  => 100,
                'currency' => 'eur'
            ));

        // form adjustments when editing a printer
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var Printer $printer */
            $printer = $event->getData();
            $form = $event->getForm();

            if ($printer && null !== $printer->getId()) {

                // get printer technology
                $technology = $printer->getTechnology();

                // prevent technology field update
                $form->add('technology', TextType::class, array(
                    'required'     => true,
                    'label'        => 'printer.form.field.technology',
                    'data'         => $technology->getName(),
                    'disabled'     => true
                ));

                // show products form
                $form->add('products', CollectionType::class, array(
                    'entry_type'    => PrinterProductType::class,
                    'entry_options' => array(
                        'label'        => false,
                        'materials'    => $technology->getMaterials(),
                        'filling_rate' => $technology->hasFillingRate()
                    ),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'attr'          => array('class' => 'printer-products-collection'),
                    'prototype_name' => 'products_prototype'
                ));

                if ($technology->getActiveMethodeVolume ()) {
                    $form->add('volumeMethode', ChoiceType::class, array(
                        'required' => true,
                        'label'    => 'printer.form.field.volume_methode',
                        'choices'  => [
                            'printer.form.field.volume_methode.matiere_object' => 0,
                            'printer.form.field.volume_methode.bounding_box' => 1,
                        ]
                    ));
                }


            }
        });

        // add a subscriber to handle the form properly
        $builder->addEventSubscriber($this->listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Printer::class
        ));
    }

    public function getName()
    {
        return 'appbundle_printer';
    }
}