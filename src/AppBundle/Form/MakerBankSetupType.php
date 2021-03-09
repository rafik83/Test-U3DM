<?php

namespace AppBundle\Form;

use AppBundle\Entity\Maker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\File;

class MakerBankSetupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('iban', TextType::class, array(
                'required' => true,
                'mapped'   => false,
                'label'    => 'IBAN'
            ))
            ->add('birthDate', BirthdayType::class, array(
                'attr'  => ['html5' => true],
                'label' => 'Date de naissance du représentant légal',
                'years' => range(date('Y') - 10, date('Y') - 100),
                'placeholder' => array(
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ),
                'view_timezone' => 'UTC'
            ))
            ->add('identityPaperFile', VichImageType::class, array(
                'required'        => true,
                'label'           => 'Pièce d\'identité',
                'allow_delete'    => true,
                'delete_label'    => 'Supprimer le document',
                'download_link'   => false,
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Maker::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_maker';
    }
}