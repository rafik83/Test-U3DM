<?php

namespace AppBundle\Form;

use AppBundle\Entity\CategoryModel;
use AppBundle\Entity\Category;
use AppBundle\EventListener\CategoryModelChooseListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;



class CategoryModelChooseType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categoryUp', EntityType::class, array(
                'required'     => true,
                'placeholder' => 'Choisissez le premier niveau de catégorie',
                'label'        => 'Niveau 1',
                'class'        => 'AppBundle\Entity\Category',
                'query_builder' => function(CategoryRepository $repo) {
                    return $repo->findCategoryLevel0();
                },
                'choice_label' => 'name',
                'mapped'=> false
            ))
            
            
            ->add('category', EntityType::class, array(
                'required'     => true,
                'placeholder' => 'Choisissez le dernier niveau de catégorie',
                'label'        => 'Niveau 2',
                'group_by' => function(Category $category) {
                    return $category->getUpCategory();
                },
                'class'        => 'AppBundle\Entity\Category',
                'query_builder' => function(CategoryRepository $repo) {
                    return $repo->findCategoryLevel1();
                },
                'choice_label' => 'name'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CategoryModel::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_category_model_choose';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }


}