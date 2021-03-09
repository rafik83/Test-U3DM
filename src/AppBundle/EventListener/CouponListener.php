<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Coupon;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class CouponListener implements EventSubscriberInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'handleCouponForm'
        );
    }

    /**
     * Handle the coupon form
     *
     * @param FormEvent $event
     */
    public function handleCouponForm(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Coupon $coupon */
        $coupon = $event->getData();

        // make sure one of the discount type is not null
        if (null === $coupon->getDiscountPercent() && null === $coupon->getDiscountAmount()) {
            $form->addError(new FormError($this->translator->trans('coupon.missing_discount_value', array(), 'validators')));
        }

        // handle discount type mutual exclusion
        if (null !== $coupon->getDiscountPercent()) {
            $coupon->setDiscountAmount(null);
        }

        // make sure launch date is after now
        if ($coupon->getLaunchDate() < new \DateTime('now', new \DateTimeZone('UTC'))) {
            $form->addError(new FormError($this->translator->trans('coupon.launch_date_before_now', array(), 'validators')));
        }

        // make sure launch date is before start date
        if ($coupon->getLaunchDate() > $coupon->getStartDate()) {
            $form->addError(new FormError($this->translator->trans('coupon.launch_date_after_start', array(), 'validators')));
        }

        // make sure end date is after start date
        if ($coupon->getEndDate() <= $coupon->getStartDate()) {
            $form->addError(new FormError($this->translator->trans('coupon.end_date_before_start', array(), 'validators')));
        }

        // make sure minimal order amount is positive
        if ($coupon->getMinOrderAmount() < 0) {
            $form->addError(new FormError($this->translator->trans('coupon.negative_min_order_amount', array(), 'validators')));
        }

        // make sure U3DM percent is positive and not above 100
        if ($coupon->getU3dmPercentPart() < 0 || $coupon->getU3dmPercentPart() > 100) {
            $form->addError(new FormError($this->translator->trans('coupon.u3dm_percent_part_limit', array(), 'validators')));
        }

        // set the remaining stock if an initial stock exists
        if (null !== $coupon->getInitialStock()) {
            $coupon->setRemainingStock($coupon->getInitialStock());
        }

    }
}