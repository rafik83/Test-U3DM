<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array(
                'required' => false,
                'mapped' => false,
                'label' => 'maker.form.field.order.file',
                'attr' => array('accept' => '.zip,.7z,.rar')
                
            ))
            /* 
            ->add('urlDownload', TextType::class, array(
                'required' => false,
                'mapped' => false,
                'label' => 'maker.form.field.order.url.download',
                'attr' => array('accept' => '.zip,.tar')
                
            ))
            **/
            
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\OrderFile'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_order_file';
    }
}