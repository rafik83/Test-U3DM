<?php

namespace AppBundle\Form;

use AppBundle\Entity\ModerationRule;
use Symfony\Component\Form\AbstractType;

use AppBundle\Form\ModerationRuleType;
use AppBundle\EventListener\ModelPortfolioImageListener;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;



class ModerationRulesType extends AbstractType
{

  /* J'ai supprimé l usage du listener */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('rules', CollectionType::class, array(
            //'required'      => true,
            'label'         => 'Règles moderation',
            'entry_type'    => ModerationRuleType::class,
            //'entry_options' => array('label' => true),
            'allow_add'     => true,
            'allow_delete'  => true,
            'by_reference'  => true,
            'attr'          => array('class' => 'moderationRules-collection')
        ));
    
        $builder->add('save', SubmitType::class, [
            'label' => 'See actions',
        ]);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ModerationRules::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_admin_moderationRules';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
