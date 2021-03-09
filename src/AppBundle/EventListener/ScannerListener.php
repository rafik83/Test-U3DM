<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Scanner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class ScannerListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ScannerListener constructor
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
            FormEvents::SUBMIT => 'handleScannerForm'
        );
    }

    /**
     * Handle the scanner form
     *
     * @param FormEvent $event
     */
    public function handleScannerForm(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Printer $printer */
        $scanner = $event->getData();

        // make sure some fields are not negative
        if (0 > $scanner->getMaxDimensions()->getX() || 0 > $scanner->getMaxDimensions()->getY() || 0 > $scanner->getMaxDimensions()->getZ()) {
            $form->addError(new FormError($this->translator->trans('scanner.negative_value', array('%field%' => $this->translator->trans('scanner.form.field.max_dimensions')), 'validators')));
        }

        if (0 > $scanner->getMinDimensions()->getX() || 0 > $scanner->getMinDimensions()->getY() || 0 > $scanner->getMinDimensions()->getZ()) {
            $form->addError(new FormError($this->translator->trans('scanner.negative_value', array('%field%' => $this->translator->trans('scanner.form.field.min_dimensions')), 'validators')));
        }
        
    }
}