<?php

namespace AppBundle\Form;

use AppBundle\Entity\Address;
use AppBundle\Entity\User;
use AppBundle\EventListener\UserListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    private $listener;

    public function __construct(UserListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                'required'   => true,
                'label_attr' => array('class' => 'no-required-display'),
                'label'      => false,
                'expanded'   => true,
                'choices'    => array(
                    'user.form.field.type.company'    => User::TYPE_COMPANY,
                    'user.form.field.type.individual' => User::TYPE_INDIVIDUAL
                )
            ))
            ->add('firstname', TextType::class, array(
                'required' => true,
                'label'    => 'user.form.field.firstname'
            ))
            ->add('lastname', TextType::class, array(
                'required' => true,
                'label'    => 'user.form.field.lastname'
            ))
            ->add('company', TextType::class, array(
                'required' => false,
                'label'    => 'Nom commercial',
                'attr'     => array('class' => 'company-only')
            ))
            ->add('companyType', TextType::class, array(
                'required' => false,
                'label'    => 'user.form.field.company_type',
                'attr'     => array('class' => 'company-only')
            ))
            ->add('defaultShippingAddress', AddressType::class, array(
                'required' => true,
                'label'    => 'user.form.field.address.shipping',
                'company_label' => 'Nom société'
            ))
            ->add('sameAddress', CheckboxType::class, array(
                'required' => false,
                'label'    => 'user.form.field.address.same'
            ))
            ->add('defaultBillingAddress', AddressType::class, array(
                'required'   => false,
                'label'      => 'user.form.field.address.billing',
                'label_attr' => array('class' => 'required'),
                'company_label' => 'Raison sociale'
            ))
            ->add('newsletter', CheckboxType::class, array(
                'required' => false,
                'label'    => 'user.form.field.newsletter'
            ))
        ;

        // handle sameAddress checkbox value
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var User $user */
                $user = $event->getData();
                if (null === $user->getDefaultShippingAddress()) {
                    $sameAddress = true;
                    // pre set some address data
                    $address = new Address();
                    $address->setFirstname($user->getFirstname());
                    $address->setLastname($user->getLastname());
                    $address->setCountry('FR');
                    $user->setDefaultShippingAddress($address);
                } else {
                    $sameAddress = $user->getDefaultShippingAddress()->isEqualTo($user->getDefaultBillingAddress());
                }
                $user->setSameAddress($sameAddress);
            }
        );

        // add a subscriber to handle the form properly
        $builder->addEventSubscriber($this->listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_profile';
    }
}