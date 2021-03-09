<?php

namespace AppBundle\Form;

use AppBundle\Entity\MakerPortfolioImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MakerPortfolioImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile', VichImageType::class, array(
                'required'        => false,
                'label'           => 'Image',
                'allow_delete'    => false,
                'download_uri'    => false,
                'imagine_pattern' => 'maker_portfolio_small'
            ))
            ->add('position', HiddenType::class, array(
                'attr' => array(
                    'class' => 'image-position',
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MakerPortfolioImage::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_maker_portfolio_image';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}