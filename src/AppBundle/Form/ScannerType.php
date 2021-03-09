<?php

namespace AppBundle\Form;

use AppBundle\Entity\Scanner;
use AppBundle\EventListener\ScannerListener;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScannerType extends AbstractType
{
    /**
     * @var ScannerListener
     */
    private $listener;

    /**
     * ScannerType constructor
     *
     * @param ScannerListener $listener
     */
    public function __construct(ScannerListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'scanner.form.field.name'
            ))
            ->add('visible', CheckboxType::class, array(
                'required' => false,
                'label'    => 'scanner.form.field.available'
            ))
            ->add('technology', EntityType::class, array(
                'required'     => true,
                'label'        => 'scanner.form.field.technology',
                'class'        => 'AppBundle\Entity\TechnologyScanner',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = true');
                },
                'choice_label' => 'name',
                'expanded'     => true
            ))
            ->add('precision', EntityType::class, array(
                'required'     => true,
                'label'        => 'scanner.form.field.precision',
                'class'        => 'AppBundle\Entity\Precision',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = true');
                },
                'choice_label' => 'name',
                'expanded'     => true
            ))
            ->add('resolution', EntityType::class, array(
                'required'     => true,
                'label'        => 'scanner.form.field.resolution',
                'class'        => 'AppBundle\Entity\Resolution',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = true');
                },
                'choice_label' => 'name',
                'expanded'     => true
            ))
            ->add('brand', TextType::class, array(
                'required' => true,
                'label'    => 'scanner.form.field.brand'
            ))
            ->add('minDimensions', DimensionsType::class, array(
                'required' => true,
                'label'    => 'scanner.form.field.min_dimensions'
            ))
            ->add('maxDimensions', DimensionsType::class, array(
                'required' => true,
                'label'    => 'scanner.form.field.max_dimensions'
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Scanner::class
        ));
    }

    public function getName()
    {
        return 'appbundle_scanner';
    }
}