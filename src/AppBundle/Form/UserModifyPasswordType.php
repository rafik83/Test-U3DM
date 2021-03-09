<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Translation\TranslatorInterface;

class UserModifyPasswordType extends AbstractType
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
        $builder
            ->add('plainPassword', RepeatedType::class, array(
                'type'           => PasswordType::class,
                'first_options'  => array('label' => 'user.form.field.new_password'),
                'second_options' => array('label' => 'user.form.field.new_password.repeat'),
                'invalid_message' => 'user.mismatch.password'
            ))

        ;

        // add error message on repeated second fields
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
            $form = $event->getForm();
            
            if ($form->get('plainPassword')->get('first')->getData() !== $form->get('plainPassword')->get('second')->getData()) {
                $form->get('plainPassword')->get('second')->addError(new FormError($this->translator->trans('user.mismatch.password', array(), 'validators')));
            }
            
            //Check password solidity
            $password = $form->get('plainPassword')->get('first')->getData();
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChar = preg_match('/[\'^£$%&*()}{@#~?!><>,|:;.\]\[\/=_+¬-]/', $password);

            if(!$uppercase || !$specialChar || !$lowercase || !$number || strlen($password) < 8) {

                $form->get('plainPassword')->get('first')->addError(new FormError($this->translator->trans('user.down.password', array(), 'validators')));

            }

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_modify_password';
    }
}