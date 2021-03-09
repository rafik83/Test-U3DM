<?php

namespace AppBundle\Form;

use AppBundle\Entity\Model;
use AppBundle\Entity\ModelLicense;
use AppBundle\Entity\Suggestion;
use AppBundle\Repository\ModelStatusRepository;
use AppBundle\EventListener\ModelListener;
use AppBundle\EventListener\SuggestionListener;
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


class SuggestionType extends AbstractType
{
    /**
     * @var SuggestionListener
     */
    private $listener;

    /**
     * PrinterType constructor
     *
     * @param SuggestionListener $listener
     */
    public function __construct(SuggestionListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('suggestionModel', CollectionType::class, array(
                'required'      => false,
                'label'         => 'Le pourcentage choisis, correspond au taux d\'appartion du modèle dans les suggestions',
                'entry_type'    => SuggestionModelType::class,
                'entry_options' => array('label' => false),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array('class' => 'suggestion-collection')
            ))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Suggestion::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_add_suggestion';
    }
}