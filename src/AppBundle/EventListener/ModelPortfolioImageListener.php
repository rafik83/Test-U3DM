<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ModelPortfolioImage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class ModelPortfolioImageListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ModelPortfolioImageListener constructor
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'handleModelPortfolioImageForm',
        );
    }

    /**
     * Handle the modelPortfolioImage form
     *
     * @param FormEvent $event
     */
    public function handleModelPortfolioImageForm(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var ModelPortfolioImage $image */
        $image = $event->getData();
        
        $fileName = $image->getPictureFile();
        if ($fileName != null) {
            $size = (filesize($fileName));
            //$form->addError(new FormError($this->translator->trans($size,  array('%field%' => $this->translator->trans('model.form.label.portfolioImages')), 'validators')));
            
            if($size < 1) {
                $form->addError(new FormError($this->translator->trans('model.size_image',  array('%field%' => ini_get('upload_max_filesize')), 'validators')));
            }
            $extension = $fileName->getClientOriginalExtension();
            echo("<script>console.log('PHP: ".$extension."');</script>");
            if ($extension != "png" && $extension != "PNG" && 
                $extension != "jpg" && $extension != "JPG" &&
                $extension != "jpeg" && $extension != "JPEG" &&
                $extension != "gif" && $extension != "GIF" && 
                $extension != "pdf" && $extension != "PDF") {
                $form->addError(new FormError($this->translator->trans('model.type_image',  array('%field%' => $this->translator->trans('model.form.label.portfolioImages')), 'validators')));
            }
        }
        
    }
}