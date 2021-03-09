<?php

namespace AppBundle\Form;

use AppBundle\Entity\CategoryModel;
use AppBundle\Entity\Category;
use AppBundle\Entity\SuggestionModel;
use AppBundle\EventListener\SuggestionModelListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use AppBundle\Entity\Model;
use AppBundle\Entity\ModelLicense;
use AppBundle\Repository\ModelStatusRepository;
use AppBundle\EventListener\ModelListener;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\TranslatorInterface;



class SuggestionModelType extends AbstractType
{
    /**
     * @var SuggestionModelListener
     */
    private $listener;

    /**
     * PrinterType constructor
     *
     * @param SuggestionModelListener $listener
     */
    public function __construct(SuggestionModelListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            
            
            ->add('percentage', IntegerType::class, array(
                'required'     => true,
                'label'        => 'Pourcentage de visibilité',
                'attr' => array(
                    'min' => '0',
                    'max' => '100',
                )
            ))

            ->add('categoryUp', EntityType::class, array(
                'required'     => true,
                'placeholder' => 'Choisissez le dernier niveau de catégorie',
                'label'        => 'Niveau 2',
                'class'        => 'AppBundle\Entity\Category',
                'query_builder' => function(CategoryRepository $repo) {
                    return $repo->findCategoryLevel0();
                },
                'choice_label' => 'name'
            ))
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SuggestionModel::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_suggestion_model';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }


}