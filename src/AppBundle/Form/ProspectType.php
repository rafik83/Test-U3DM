<?php

namespace AppBundle\Form;

use AppBundle\EventListener\ProspectListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProspectType extends AbstractType
{
    /**
     * Field constants
     */
    const FIELD_COMPANY = 'company';

    /**
     * @var ProspectListener $listener
     */
    protected $listener;


    /**
     * ProspectType constructor.
     *
     * @param ProspectListener $listener
     */
    public function __construct(ProspectListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Prospect'
        ));
    }
}