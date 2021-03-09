<?php

namespace AppBundle\Form;

use AppBundle\Entity\Address;
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

class UserRegistrationType extends AbstractType
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
            ->add('firstname', TextType::class, array(
                'required' => true,
                'label'    => 'user.form.field.firstname'
            ))
            ->add('lastname', TextType::class, array(
                'required' => true,
                'label'    => 'user.form.field.lastname'
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'label'    => 'user.form.field.email'
            ))
            ->add('plainPassword', PasswordType::class, array(
                'required' => true,
                'label'    => 'user.form.field.password'
            ))
            ->add('defaultBillingAddress', AddressType::class, array(
                'required'   => false,
                'label'      => 'Adresse',
                'label_attr' => array('class' => 'required'),
                'hide_firstname' => true,
                'hide_lastname'  => true
            ))
            ->add('newsletter', CheckboxType::class, array(
                'required' => false,
                'label'    => 'user.form.field.newsletter',
                'data'     => true// default value
            ))
        ;

        // default values to avoid getting a null address
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var User $user */
                $user = $event->getData();
                if (null === $user->getDefaultBillingAddress()) {
                    // pre set some address data
                    $address = new Address();
                    $address->setCountry('FR');
                    $user->setDefaultBillingAddress($address);
                }
            }
        );

        // add error messages
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
            $form = $event->getForm();
            
            //Check password solidity
            $password = $form->get('plainPassword')->getData();
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChar = preg_match('/[\'^£$%&*()}{@#~?!><>,|:;.\]\[\/=_+¬-]/', $password);

            if(!$uppercase || !$specialChar || !$lowercase || !$number || strlen($password) < 8) {

                $form->get('plainPassword')->addError(new FormError($this->translator->trans('user.down.password', array(), 'validators')));
            }

            /** @var Address $billingAddress */
            $billingAddress = $event->getData()->getDefaultBillingAddress();

            if (null === $billingAddress) {
                $form->get('defaultBillingAddress')->get('street1')->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.street1')), 'validators')));
            } else {
                if (null === $billingAddress->getStreet1()) {
                    $form->get('defaultBillingAddress')->get('street1')->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.street1')), 'validators')));

                }
                if (null === $billingAddress->getZipcode()) {
                    $form->get('defaultBillingAddress')->get('zipcode')->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.zip_code')), 'validators')));

                }
                if (null === $billingAddress->getCity()) {
                    $form->get('defaultBillingAddress')->get('city')->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.city')), 'validators')));

                }
                if (null === $billingAddress->getCountry()) {
                    $form->get('defaultBillingAddress')->get('country')->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.country')), 'validators')));

                }
                if (null === $billingAddress->getTelephone()) {
                    $form->get('defaultBillingAddress')->get('telephone')->addError(new FormError($this->translator->trans('user.address.billing.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.telephone')), 'validators')));

                }
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
        return 'appbundle_user_register';
    }
}