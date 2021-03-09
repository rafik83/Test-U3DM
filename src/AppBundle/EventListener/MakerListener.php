<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Maker;
use AppBundle\Entity\MakerPortfolioImage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class MakerListener implements EventSubscriberInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'handleMakerForm'
        );
    }

    /**
     * Handle the maker form
     *
     * @param FormEvent $event
     */
    public function handleMakerForm(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Maker $maker */
        $maker = $event->getData();

        if ('maker' === $form->getName()) {

            // make sure required fields are set
            if (false === $maker->isPrinter() && false === $maker->isDesigner()) {
                $form->addError(new FormError($this->translator->trans('maker.choose_type', array(), 'validators')));
            }

            // check Company field for maker (not useful as "required" is set on address.company field in from type, but we keep it for reference)
            if (null === $maker->getAddress()->getCompany()) {
                $form->addError(new FormError($this->translator->trans('maker.address.missing_required_field', array('%field%' => $this->translator->trans('Raison sociale')), 'validators')));
            }

        } elseif ('maker_details' === $form->getName()) {

            // make sure there is a logo
            if (null === $maker->getProfilePictureFile() && null === $maker->getProfilePictureName()) {
                $form->addError(new FormError($this->translator->trans('missing_required_field.logo', array(),'validators')));
            }

            // make sure there is at least one portfolio image
            foreach($maker->getPortfolioImages() as $image) {
                /** @var MakerPortfolioImage $image */
                if (null === $image->getPictureFile() && null === $image->getPictureName()) {
                    $maker->removePortfolioImage($image);
                }
            }
            if (0 === $maker->getPortfolioImages()->count()) {
                $form->addError(new FormError($this->translator->trans('missing_required_field.portfolio', array(),'validators')));
            }

            // handle pickup address
            if ($maker->hasPickup()) {
                // make sure the pickup address is valid
                $pickupAddress = $maker->getPickupAddress();
                if (null === $pickupAddress->getFirstname()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.firstname')), 'validators')));
                }
                if (null === $pickupAddress->getLastname()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.lastname')), 'validators')));
                }
                if (null === $pickupAddress->getCompany()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.company')), 'validators')));
                }
                if (null === $pickupAddress->getStreet1()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.street1')), 'validators')));
                }
                if (null === $pickupAddress->getZipcode()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.zip_code')), 'validators')));
                }
                if (null === $pickupAddress->getCity()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.city')), 'validators')));
                }
                if (null === $pickupAddress->getCountry()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.country')), 'validators')));
                }
                if (null === $pickupAddress->getTelephone()) {
                    $form->addError(new FormError($this->translator->trans('maker.pickup_address.missing_required_field', array('%field%' => $this->translator->trans('address.form.field.telephone')), 'validators')));
                }
            } else {
                // reset the pickup address to null (the address entity will be removed thanks to orphanRemoval annotation on property)
                $maker->setPickupAddress(null);
            }
        }
    }
}