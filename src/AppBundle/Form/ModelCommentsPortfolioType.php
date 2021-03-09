<?php

namespace AppBundle\Form;

use AppBundle\Entity\ModelCommentsPortfolio;
use AppBundle\EventListener\ModelCommentsPortfolioListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ModelCommentsPortfolioType extends AbstractType
{

    /**
     * @var ModelCommentsPortfolioListener
     */
    private $listener;

    /**
     * PrinterType constructor
     *
     * @param ModelCommentsPortfolioListener $listener
     */
    public function __construct(ModelCommentsPortfolioListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile', VichImageType::class, array(
                'required'        => false,
                'label'           => ' ',
                'allow_delete'    => false,
                'download_uri'    => false,
                'imagine_pattern' => 'comment_portfolio',
                'attr' => [
                    'accept' => '.png,.jpg,.jpeg'],
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
            ->add('position', HiddenType::class, array(
                'attr' => array(
                    'class' => 'image-position',
                ),
            ))
        ;

        

        $builder->addEventSubscriber($this->listener);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ModelCommentsPortfolio::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_comment_portfolio';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}