<?php

namespace AppBundle\Form;

use AppBundle\Entity\Quotation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuotationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        //Close option is for admin
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
        if($options['refuse_quotation'] == true){

            $disableActionRefuse = true;
        } else {

            $disableActionRefuse = false;
        }


        $builder
            ->add('internalReference', TextType::class, array(
                'label' => 'maker.project.form.field.internal.reference.label',
                'required' => false,
                'disabled' => $disabled
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'admin.coupon.form.field.description.label',
                'required' => true,
                'attr' => array('rows' => 10),
                'disabled' => $disabled

            ))
            ->add('productionTime', IntegerType::class, array(
                'label' => 'maker.project.form.field.internal.production.time.label',
                'required' => true,
                'disabled' => $disabled
            ))
            ->add('lines', CollectionType::class, array(
                'required'      => true,
                'label'         => false,//'maker.quotation.line.form.field.line',
                'entry_type'    => QuotationLineType::class,
                'entry_options' => array('label' => false,'view_only'=> $options['view_only'],'user_type'=> $options['user_type']),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array('class' => 'quotation-lines-collection'),
                'prototype_name' => 'quotation_lines_prototype'
            ));

            if($options['user_type'] == 'maker'){
                $builder
                    ->add('save', SubmitType::class, array(
                        'label' => 'maker.project.form.button.save.label'
                    ))
                    ->add('sent', SubmitType::class, array(
                        'label' => 'maker.project.form.button.sent.label'
                    ));

            } elseif($options['user_type'] == 'admin'){
                $builder
                    ->add('save', SubmitType::class, array(
                        'label' => 'maker.project.form.button.save.label',
                        'disabled' => $disabled,
                        'attr' => array('class' => 'btn-space btn-primary')
                    ))
                    ->add('accept', SubmitType::class, array(
                        'label' => 'admin.quotation.see.dispatch',
                        'disabled' => $disabled,
                        'attr' => array('class' => 'btn-space btn-primary')
                    ))
                    ->add('refuse', SubmitType::class, array(
                        'label' => 'admin.quotation.see.not.dispatch',
                        'disabled' => $disableActionRefuse,
                        'attr' => array('class' => 'btn-space btn-primary')
                    ));

            } else {
                $builder
                    ->add('save', SubmitType::class, array(
                        'label' => 'maker.project.form.button.save.label',
                        'disabled' => $disabled,
                        'attr' => array('class' => 'btn-space btn-primary')
                    ));
            }

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Quotation',
            'user_type' => 'maker',
            'view_only' => false,
            'refuse_quotation' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_quotation';
    }
}