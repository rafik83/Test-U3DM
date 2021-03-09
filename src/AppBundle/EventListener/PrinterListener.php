<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Printer;
use AppBundle\Entity\PrinterProduct;
use AppBundle\Entity\PrinterProductFinishing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class PrinterListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * PrinterListener constructor
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
            FormEvents::SUBMIT => 'handlePrinterForm'
        );
    }

    /**
     * Handle the printer form
     *
     * @param FormEvent $event
     */
    public function handlePrinterForm(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Printer $printer */
        $printer = $event->getData();

        // make sure some fields are not negative
        if (0 > $printer->getSetupPrice()) {
            $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans('printer.form.field.setup_price')), 'validators')));
        }
        if (0 > $printer->getMaxDimensions()->getX() || 0 > $printer->getMaxDimensions()->getY() || 0 > $printer->getMaxDimensions()->getZ()) {
            $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans('printer.form.field.max_dimensions')), 'validators')));
        }
        if (0 > $printer->getMinVolume()) {
            $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans('printer.form.field.min_volume')), 'validators')));
        }
        foreach ($printer->getProducts() as $product) {
            /** @var PrinterProduct $product */
            if (0 > $product->getPrice100()) {
                $fieldLabel = 'printer_product.form.field.price_default';
                if ($printer->getTechnology()->hasFillingRate()) {
                    $fieldLabel = 'printer_product.form.field.price_100';
                }
                $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans($fieldLabel)), 'validators')));
            }
            if (null !== $product->getPrice50() && 0 > $product->getPrice50()) {
                $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans('printer_product.form.field.price_50')), 'validators')));
            }
            if (null !== $product->getPrice25() && 0 > $product->getPrice25()) {
                $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans('printer_product.form.field.price_25')), 'validators')));
            }
            foreach ($product->getFinishings() as $finishing) {
                /** @var PrinterProductFinishing $finishing */
                if (0 > $finishing->getPrice()) {
                    $form->addError(new FormError($this->translator->trans('printer.negative_value', array('%field%' => $this->translator->trans('printer_product.form.field.finishings')), 'validators')));
                }
            }
        }
    }
}