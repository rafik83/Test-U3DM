<?php

namespace AppBundle\Form;

use AppBundle\Entity\Maker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MakerDesignAutoModerationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designAutoModeration', CheckboxType::class, array(
                'required' => false,
                'label'    => 'Activer la modération automatique'
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
        return 'appbundle_user_maker_design_auto_moderation';
    }
}