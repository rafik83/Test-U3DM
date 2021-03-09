<?php

namespace AppBundle\Form;

use AppBundle\Entity\Model;
use AppBundle\Entity\ModelLicense;
use AppBundle\Repository\ModelStatusRepository;
use AppBundle\EventListener\ModelListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Translation\TranslatorInterface;


class AdminModelType extends AbstractType
{
    /**
     * @var ModelListener
     */
    private $listener;

    /**
     * PrinterType constructor
     *
     * @param ModelListener $listener
     */
    public function __construct(ModelListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => "model.form.label.name",
                'attr' => array(
                    'placeholder' => 'Un nom descriptif est conseillé')
            ))
            ->add('description', TextareaType::class, array(
                'required' => true,
                'label'    => "model.form.label.description",
                'attr' => array(
                    'placeholder' => 'Décrivez au mieux votre modèle 3D',
                    'rows'     => '7')
            ))
            ->add('caracteristique', TextareaType::class, array(
                'required' => true,
                'label'    => "model.form.label.caracteristique",
                'attr' => array(
                    'placeholder' => 'Ex :
                    Type de fichier : .obj & .slt
                    Instruction pour imprimer le modèle
                    ...',
                    'rows'     => '4')
            ))
            ->add('licences', EntityType::class, array(
                'required'     => true,
                'placeholder' => 'Choisissez une licence',
                'label'        => 'model.form.label.licences',
                'class'        => 'AppBundle\Entity\ModelLicense',
                'choice_label' => 'name',
                'attr' => array(
                    'class' => 'license'),
                'choice_attr' => function (ModelLicense $license) {
                    return ['text' => 'test' ];
                }
            ))
            ->add('price_tax_excl', MoneyType::class, array(
                'label' => 'model.form.label.price_tax_excl',
                'required' => false,
                'divisor'  => 100,
                'currency' => 'eur'
            ))
            ->add('tags', TextareaType::class, array(
                'required' => true,
                'label'    => 'model.form.label.tags',
                'attr' => array(
                    'placeholder'     => 'Séparez tous vos tag d\'un espace, ex :
                    voiture miniature petite reproduction course',
                    'title'     => 'Veillez à bien espacez vos tags d\'un espace')
            ))
            ->add('categoryModel', CollectionType::class, array(
                'required'      => true,
                'label'         => 'model.form.label.categoryModel',
                'entry_type'    => CategoryModelChooseType::class,
                'entry_options' => array('label' => false),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array('class' => 'categoryModel-collection')
            ))
            ->add('portfolioImages', CollectionType::class, array(
                'label'         => 'model.form.label.portfolioImages',
                'entry_type'    => ModelPortfolioImageType::class,
                'entry_options' => array(
                    'label'        => false,
                ),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array(
                    'class' => 'portfolio-collection')
            ))/*
            ->add('correctionReason', TextareaType::class, array(
                'label'    => 'Raison de la demande de correction ou de suppression',
                'required' => false,
                'attr'     => array('rows' => 5)
            ))*/
            //->add('save', SubmitType::class, ['label' => 'model.form.label.save'])
            //->add('add', SubmitType::class, ['label' => 'model.form.label.add'])
        ;

        $builder->addEventSubscriber($this->listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Model::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_admin_model';
    }
}