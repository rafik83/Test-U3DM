<?php

namespace AppBundle\Form;

use AppBundle\Entity\ModelPortfolioImage;
use AppBundle\EventListener\ModelPortfolioImageListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ModelPortfolioImageType extends AbstractType
{

    /**
     * @var ModelPortfolioImageListener
     */
    private $listener;

    /**
     * PrinterType constructor
     *
     * @param ModelPortfolioImageListener $listener
     */
    public function __construct(ModelPortfolioImageListener $listener)
    {
        $this->listener = $listener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile', VichImageType::class, array(
                'required'        => false,
                'label'           => 'Image',
                'allow_delete'    => false,
                'download_uri'    => false,
                'imagine_pattern' => 'model_portfolio_small'
            ))
            ->add('position', HiddenType::class, array(
                'attr' => array(
                    'class' => 'image-position',
                ),
            ))
        ;

        // pre set data event
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var ModelPortfolioImage $portfolio */
                $portfolio = $event->getData();
                $form = $event->getForm();
                // set pickup address default value if possible (if not used, it would be set back to null via the listener)
                if ($portfolio == null) {
                    $form
                    ->add('pictureFile', VichImageType::class, array(
                        'required'        => true,
                        'label'           => 'Image',
                        'allow_delete'    => false,
                        'download_uri'    => false,
                        'imagine_pattern' => 'model_portfolio_small'
                    ));
                }else {
                    $form
                    ->add('pictureFile', VichImageType::class, array(
                        'required'        => false,
                        'label'           => 'Image',
                        'allow_delete'    => false,
                        'download_uri'    => false,
                        'imagine_pattern' => 'model_portfolio_small'
                    ));
                }
            }
        );

        $builder->addEventSubscriber($this->listener);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ModelPortfolioImage::class,
        ));
    }

    public function getName()
    {
        return 'appbundle_user_model_portfolio_image';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}