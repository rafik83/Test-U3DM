<?php

namespace AppBundle\Form;

use AppBundle\Entity\ModelComments;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Vich\UploaderBundle\Form\Type\VichFileType;


class ModelCommentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, array(
                'required' => true,
                'label'    => "Commentaire",
                'attr' => array(
                    'placeholder' => 'Saississez votre commentaire',
                    'rows'     => '2')
            ))
            ->add('portfolioImages', CollectionType::class, array(
                'label'         => 'model.form.label.portfolioImages',
                'entry_type'    => ModelCommentsPortfolioType::class,
                'entry_options' => array(
                    'label'        => false,
                ),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array(
                    'class' => 'portfolio-collection')
            ))
            
            
        ;
        if ($options['admin_user']) {

            $builder
                ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label' => 'Activer'

            ));

        } else {
            $builder
                ->add('save', SubmitType::class, [
                    'label' => 'Enregistrer',
                    'attr' => ['class' => 'btn btn-response']
                    ])
            ;
            
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ModelComments::class,
            'admin_user' => false
        ));
    }

    public function getName()
    {
        return 'appbundle_add_model_comment';
    }
}