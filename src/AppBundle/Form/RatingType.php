<?php

namespace AppBundle\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class RatingType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * UserRegistrationType constructor
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        if ($options['admin_user']) {

            $builder

                ->add('rate', ChoiceType::class, array(
                    'label' => 'Note',
                    'required' => true,
                    'choices'  => array(
                        '0 - très mécontent' => 0,
                        '1 - Mécontent' => 1,
                        '2 - Non satisfait' => 2,
                        '3 - Moyennement satisfait' => 3,
                        '4 - Satisfait' => 4,
                        '5 - Très satisfait' => 5
                    ),
                    'placeholder' => 'Sélectionnez une note'
                ))
                ->add('comment', TextareaType::class, array(
                    'required' => false,
                    'label'    => 'Commentaire',
                    'attr'     => array('rows' => 4)
                ))
                ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Activer'
                ));

        } else {
            $builder
            ->add('comment', TextareaType::class, array(
                'required' => false,
                'label'    => 'Commentaire',
                'attr'     => array('rows' => 4)
            ));
            
            $builder
                ->add('rate', hiddenType::class)
            ;

        }


        // add error message on repeated second fields
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
            $form = $event->getForm();
            
            if (($form->get('comment')->getData() == null ) and  ($form->get('rate')->getData() == 0 ) or ($form->get('rate')->getData() == null )) {
                //$form->get('comment')->addError(new FormError($this->translator->trans('Message.order.rating.norate', array(), 'validators')));
                $form->addError(new FormError($this->translator->trans('rate.no_null', array(), 'validators')));

            }


        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Rating',
            'admin_user' => false
        ));
    }



    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'appbundle_rating';
    }

}