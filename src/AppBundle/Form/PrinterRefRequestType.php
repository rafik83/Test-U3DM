<?php

namespace AppBundle\Form;

use AppBundle\Entity\PrinterRefRequest;
use AppBundle\Entity\Technology;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrinterRefRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                'required' => true,
                'label'    => 'Type',
                'choices'  => array(
                    'Technologie' => PrinterRefRequest::TYPE_TECHNOLOGY,
                    'Matériau'    => PrinterRefRequest::TYPE_MATERIAL,
                    'Couleur'     => PrinterRefRequest::TYPE_COLOR,
                    'Finition'    => PrinterRefRequest::TYPE_FINISHING
                )
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'Nom'
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label'    => 'Description',
                'attr'     => array('rows' => 4)
            ))
            ->add('fillingRate', CheckboxType::class, array(
                'required' => false,
                'label'    => 'Cette technologie permet de gérer un taux de remplissage'
            ))
            ->add('technologies', EntityType::class, array(
                'required'      => false,
                'label'         => 'Technologies liées',
                'class'         => Technology::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
                },
                'choice_label'  => 'name',
                'expanded'      => false,
                'multiple'      => true
            ))
            ->add('comments', TextareaType::class, array(
                'required' => false,
                'label'    => 'Commentaires',
                'attr'     => array('rows' => 4)
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PrinterRefRequest'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_printer_ref_request';
    }
}