<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                'required' => true,
                'label'    => 'tag.form.field.type.label',
                'choices'  => array(
                    'tag.type.unknown'    => Tag::TYPE_UNKNOWN,
                    'tag.type.domain'     => Tag::TYPE_DOMAIN,
                    'tag.type.technology' => Tag::TYPE_TECHNOLOGY
                )
            ))
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'tag.form.field.name.label'
            ))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label'    => 'tag.form.field.enabled.label'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Tag'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_tag';
    }
}