<?php

namespace AppBundle\Form;

use AppBundle\Entity\Maker;
use AppBundle\EventListener\MakerListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MakerDetailsType extends AbstractType
{
    private $listener;

    public function __construct(MakerListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class, array(
                'required' => true,
                'label'    => 'Nom commercial'
            ))
            ->add('available', CheckboxType::class, array(
                'required' => false,
                'label'    => 'maker.form.field.available'
            ))
            ->add('profilePictureFile', VichImageType::class, array(
                'required'        => false, // requirement check will be done in form listener
                'label'           => 'Logo',
                'allow_delete'    => false,
                'delete_label'    => 'Supprimer le logo',
                'download_uri'    => false,
                'imagine_pattern' => 'maker_profile_small',
                'attr'            => array('class' => 'profile-logo'),
                'label_attr'      => array('class' => 'required')
            ))
            ->add('portfolioImages', CollectionType::class, array(
                'label'         => 'Portfolio',
                'entry_type'    => MakerPortfolioImageType::class,
                'entry_options' => array(
                    'label'        => false,
                ),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'attr'          => array('class' => 'portfolio-collection')
            ))
            ->add('bio', TextareaType::class, array(
                'required' => true,
                'label'    => 'maker.form.field.bio',
                'attr'     => array('rows' => 4)
            ))
            ->add('pickup', CheckboxType::class, array(
                'required' => false,
                'label'    => 'maker.form.field.pickup'
            ))
            ->add('pickupAddress', AddressType::class, array(
                'required' => false,
                'label'    => 'maker.form.field.pickup_address',
                'label_attr' => array('class' => 'required'),
                'company_label' => 'Nom société'
            ))
        ;

        // pre set data event
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Maker $maker */
                $maker = $event->getData();
                // set pickup address default value if possible (if not used, it would be set back to null via the listener)
                if (null === $maker->getPickupAddress() && null !== $maker->getAddress()) {
                    $pickupAddress = clone $maker->getAddress();
                    $maker->setPickupAddress($pickupAddress);
                }
            }
        );

        // add a subscriber to handle the form properly
        $builder->addEventSubscriber($this->listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Maker::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_maker_details';
    }
}