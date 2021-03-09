<?php

namespace AppBundle\Form;

use AppBundle\Entity\Coupon;
use AppBundle\Entity\User;
use AppBundle\EventListener\CouponListener;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponType extends AbstractType
{
    private $listener;

    public function __construct(CouponListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // enabled checkbox must be editable at all time
        $builder
            ->add('enabled', CheckboxType::class, array(
                'label' => 'admin.coupon.form.field.enabled.label',
                'required' => false
            ))
        ;

        // coupon can not be edited after launch date, so add all the fields in a pre set data event
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Coupon $coupon */
                $form = $event->getForm();
                $coupon = $event->getData();
                $editable = true;
                if (null !== $coupon && null !== $coupon->getId()) {
                    if ($coupon->getLaunchDate() <= new \DateTime('now', new \DateTimeZone('UTC'))) {
                        $editable = false;
                    }
                }
                // add fields
                $form
                    ->add('code', TextType::class, array(
                        'label' => 'admin.coupon.form.field.code.label',
                        'required' => true,
                        'disabled' => !$editable
                    ))
                    ->add('type', TextType::class, array(
                        'label' => 'admin.coupon.form.field.type.label',
                        'required' => true,
                        'disabled' => !$editable
                    ))
                    ->add('label', TextType::class, array(
                        'label' => 'admin.coupon.form.field.label.label',
                        'required' => true,
                        'disabled' => !$editable
                    ))
                    ->add('description', TextareaType::class, array(
                        'label' => 'admin.coupon.form.field.description.label',
                        'required' => true,
                        'disabled' => !$editable
                    ))
                    ->add('discountPercent', NumberType::class, array(
                        'label' => 'admin.coupon.form.field.discount_percent.label',
                        'required' => false,
                        'disabled' => !$editable
                    ))
                    ->add('discountAmount', MoneyType::class, array(
                        'label' => 'admin.coupon.form.field.discount_amount.label',
                        'required' => false,
                        'disabled' => !$editable,
                        'divisor'  => 100,
                        'currency' => 'eur'
                    ))
                    ->add('minOrderAmount', MoneyType::class, array(
                        'label' => 'admin.coupon.form.field.min_order_amount.label',
                        'required' => true,
                        'disabled' => !$editable,
                        'divisor'  => 100,
                        'currency' => 'eur'
                    ))
                    ->add('launchDate', DateTimeType::class, array(
                        'label' => 'admin.coupon.form.field.launch_date.label',
                        'required' => true,
                        'disabled' => !$editable,
                        'years' => array(date('Y'), date('Y') + 1),
                        'model_timezone' => 'UTC',
                        'view_timezone' => 'Europe/Paris'
                    ))
                    ->add('startDate', DateTimeType::class, array(
                        'label' => 'admin.coupon.form.field.start_date.label',
                        'required' => true,
                        'disabled' => !$editable,
                        'years' => array(date('Y'), date('Y') + 1),
                        'model_timezone' => 'UTC',
                        'view_timezone' => 'Europe/Paris'
                    ))
                    ->add('endDate', DateTimeType::class, array(
                        'label' => 'admin.coupon.form.field.end_date.label',
                        'required' => true,
                        'disabled' => !$editable,
                        'years' => array(date('Y'), date('Y') + 1),
                        'model_timezone' => 'UTC',
                        'view_timezone' => 'Europe/Paris'
                    ))
                    ->add('maxUsagePerCustomer', IntegerType::class, array(
                        'label' => 'admin.coupon.form.field.max_per_customer.label',
                        'required' => false,
                        'disabled' => !$editable
                    ))
                    ->add('initialStock', IntegerType::class, array(
                        'label' => 'admin.coupon.form.field.initial_stock.label',
                        'required' => false,
                        'disabled' => !$editable
                    ))
                    ->add('u3dmPercentPart', NumberType::class, array(
                        'label' => 'admin.coupon.form.field.u3dm_percent_part.label',
                        'required' => true,
                        'disabled' => !$editable
                    ))
                    ->add('customers', EntityType::class, array(
                        'label' => 'admin.coupon.form.field.customers.label',
                        'required' => false,
                        'disabled' => !$editable,
                        'class' => 'AppBundle:User',
                        'choice_label' => function ($user) {
                            /** @var User $user */
                            $res = '';
                            $res .= strtoupper($user->getLastname()). ' ' . $user->getFirstname();
                            if (null !== $user->getCompany()) {
                                $res .= ' (' . $user->getCompany() . ')';
                            }
                            return $res;
                        },
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('u')
                                ->orderBy('u.lastname', 'ASC');
                        },
                        'multiple' => true,
                        'expanded' => false
                    ))
                ;
            }
        );

        // add a subscriber to handle the form properly
        $builder->addEventSubscriber($this->listener);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Coupon'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_coupon';
    }
}