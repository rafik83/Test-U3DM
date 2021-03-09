<?php

namespace AppBundle\Form;

use AppBundle\Entity\Maker;
use AppBundle\EventListener\MakerListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MakerType extends AbstractType
{
    private $listener;

    public function __construct(MakerListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('printer', CheckboxType::class, array(
                'required' => false,
                'label'    => 'maker.form.field.printer'
            ))
            ->add('designer', CheckboxType::class, array(
                'required' => false,
                'label'    => 'maker.form.field.designer'
            ))
            ->add('company', TextType::class, array(
                'required' => true,
                'label'    => 'Nom commercial'
            ))
            ->add('companyType', TextType::class, array(
                'required' => false,
                'label'    => 'user.form.field.company_type'
            ))
            ->add('webSite', TextType::class, array(
                'required' => true,
                'label'    => 'user.form.field.web_site'
            ))
            ->add('siren', TextType::class, array(
                'required' => true,
                'label'    => 'maker.form.field.siren'
            ))
            ->add('vatNumber', TextType::class, array(
                'required' => false,
                'label'    => 'maker.form.field.vat_number'
            ))
            ->add('address', AddressType::class, array(
                'required' => true,
                'label'    => 'user.form.field.address',
                'hide_firstname' => true,
                'hide_lastname'  => true,
                'company_label'  => 'Raison sociale',
                'company_required' => true
            ))
        ;

        // bank setup fields
        $builder
            ->add('firstname', TextType::class, array(
                'required' => true,
                'label'    => 'Prénom'
            ))
            ->add('lastname', TextType::class, array(
                'required' => true,
                'label'    => 'Nom'
            ))
            ->add('birthDate', BirthdayType::class, array(
                'attr'  => ['html5' => true],
                'label' => 'Né le',
                'years' => range(date('Y') - 10, date('Y') - 100),
                'placeholder' => array(
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ),
                'view_timezone' => 'UTC'
            ));
        if (false === $options['hide_identity_paper']) {
            $builder
                ->add('identityPaperFile', VichImageType::class, array(
                    'required' => $options['required_identity_paper'],
                    'label' => 'Recto',
                    'allow_delete' => false,
                    'delete_label' => 'Supprimer le document',
                    'download_link' => false,
                    'attr' => [
                        'accept' => '.png,.jpg,.jpeg'],
                    //'download_uri'    => 'media/cache/identity_paper/',
                    //'download_label'  => 'Visualiser le document',
                    'imagine_pattern' => 'identity_paper',
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier est trop volumineux. Sa taille ne doit pas dépasser 5 Mo',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/jpg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => 'Le format du fichier doit être JPG ou PNG',
                        ])
                    ]
                ))
                ->add('identityPaperFileVerso', VichImageType::class, array(
                    'required' => $options['required_identity_paper'],
                    'label' => 'Verso',
                    'allow_delete' => false,
                    'delete_label' => 'Supprimer le document',
                    'download_link' => false,
                    'attr' => [
                        'accept' => '.png,.jpg,.jpeg'],
                    //'download_uri'    => 'media/cache/identity_paper/',
                    //'download_label'  => 'Visualiser le document',
                    'imagine_pattern' => 'identity_paper',
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier est trop volumineux. Sa taille ne doit pas dépasser 5 Mo',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/jpg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => 'Le format du fichier doit être JPG ou PNG',
                        ])
                    ]
                ));
        }
        $builder
            ->add('iban', TextType::class, array(
                'required' => $options['required_iban'],
                'mapped'   => false,
                'label'    => $options['label_iban']
            ))
        ;

        // pre set data event
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Maker $maker */
                $maker = $event->getData();
                // add CGUV checkbox if we are creating an account
                if (null === $maker || null === $maker->getId()) {
                    $form = $event->getForm();
                    $form->add('termsAccepted', CheckboxType::class, array(
                        'mapped'      => false,
                        'label'       => false,// label will be set up in twig directly
                        'constraints' => new IsTrue(array('message' => 'Vous devez accepter les conditions générales d\'utilisation et de vente pour créer un compte Maker.')),
                    ));
                }
            }
        );

        // add a subscriber to handle the form properly
        $builder->addEventSubscriber($this->listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'    => Maker::class,
            'required_iban' => true,
            'required_identity_paper' => true,
            'label_iban' => 'IBAN',
            'hide_identity_paper' => false
        ));
    }

    public function getName()
    {
        return 'appbundle_user_maker';
    }
}