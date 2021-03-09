<?php

namespace AppBundle\Form;

use AppBundle\Entity\ModerationRule;
use Symfony\Component\Form\AbstractType;
use AppBundle\EventListener\ModelPortfolioImageListener;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;



class ModerationRuleType extends AbstractType
{

  /* J'ai supprimé l usage du listener */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expression', TextType::class, array(
                'required' => true,
                'label'    => 'admin.moderation.rule.list.column.expression',
                'attr' => array(
                    'placeholder' => 'admin.moderation.rule.placeholder.column.expression')
            ))
            ->add('replace', TextType::class, array(
                'required' => true,
                'label'    => 'admin.moderation.rule.list.column.replace',
                'attr' => array(
                    'placeholder' => 'admin.moderation.rule.placeholder.column.replace')
            ))
            ->add('needModerate', CheckboxType::class, array(
                'required' => false,
                'label'    => 'admin.moderation.rule.list.column.moderation'
            ))
            ->add('position', NumberType::class, array(
                'required' => true,
                'label'    => 'admin.moderation.rule.list.column.position',
                'attr' => array(
                    'placeholder' => 'admin.moderation.rule.placeholder.column.position')
            ))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ModerationRule::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_admin_moderationRule';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}