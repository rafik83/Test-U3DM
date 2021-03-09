<?php

namespace AppBundle\Form;

use AppBundle\Entity\Setting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'label'    => 'admin.setting.form.field.name.label',
                'disabled' => true
            ))
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Setting $setting */
                $setting = $event->getData();
                $form = $event->getForm();
                switch ($setting->getType()) {
                    case Setting::TYPE_STRING:
                        $form->add('stringValue', TextType::class, array(
                            'required' => true,
                            'label'    => 'admin.setting.form.field.value.label'
                        ));
                        break;
                    case Setting::TYPE_INT:
                        $form->add('intValue', IntegerType::class, array(
                            'required' => true,
                            'label'    => 'admin.setting.form.field.value.label'
                        ));
                        break;
                    case Setting::TYPE_FLOAT:
                        $form->add('floatValue', NumberType::class, array(
                            'required' => true,
                            'label'    => 'admin.setting.form.field.value.label'
                        ));
                        break;
                    case Setting::TYPE_PERCENT:
                        $form->add('floatValue', NumberType::class, array(
                            'required' => true,
                            'label'    => 'admin.setting.form.field.value.percent.label'
                        ));
                        break;
                    case Setting::TYPE_MONEY:
                        $form->add('intValue', MoneyType::class, array(
                            'required' => true,
                            'label'    => 'admin.setting.form.field.value.money.label',
                            'divisor'  => 100,
                            'currency' => 'eur'
                        ));
                        break;
                    default:
                        break;
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Setting'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_setting';
    }
}