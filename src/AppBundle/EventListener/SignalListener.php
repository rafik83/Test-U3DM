<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Signal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use AppBundle\Event\SignalEvent;
use AppBundle\Event\SignalEvents;
use AppBundle\Service\SendinBlue;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;

class SignalListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    private $router;
    private $sendinBlue;

    /**
     * SignalListener constructor
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, SendinBlue $sendinBlue)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->sendinBlue = $sendinBlue;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            SignalEvents::POST_ADMIN_SENT_TO_SIGNAL => 'sendMakerNotificationForSignal',
            SignalEvents::POST_ADMIN_SENT_TO_CUSTOMER_SIGNAL => 'sendCustomerNotificationForSignal',
        );
    }

    /**
     * Send maker e-mail notification when admin sends quotation to correction
     *
     * @param SignalEvent $event
     */
    public function sendMakerNotificationForSignal(SignalEvent $event)
    {
        $signal = $event->getSignal();

        // email vars
        $emailVars = array(
            'ModeleNom'         => $signal->getModel()->getName(),
            'MakerNom'          => $signal->getModel()->getMaker()->getCompany(),
            'TypeSignalement'   => $signal->getSignalRef()->getSignalName(),
            'DescSignalement'   => $signal->getDescription(),
            'accountUrl'        => $this->router->generate('user_maker_model_modify', array('id' => $signal->getModel()->getId()), $this->router::ABSOLUTE_URL)
        );

        // send maker e-mail
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_MODEL_SIGNAL_MAKER_NOTIFICATION,
            $signal->getModel()->getMaker()->getUser()->getEmail(),
            $signal->getModel()->getMaker()->getFullname(),
            $emailVars
        );
    }

    /**
     * Send maker e-mail notification when admin sends quotation to correction
     *
     * @param SignalEvent $event
     */
    public function sendCustomerNotificationForSignal(SignalEvent $event)
    {
        $signal = $event->getSignal();

        // email vars
        $emailVars = array(
            'ModeleNom'         => $signal->getModel()->getName(),
            'TypeSignalement'   => $signal->getSignalRef()->getSignalName(),
            'DescSignalement'   => $signal->getDescription(),
            'accountUrl'        => $this->router->generate('user_maker_model_modify', array('id' => $signal->getModel()->getId()), $this->router::ABSOLUTE_URL)
        );

        // send maker e-mail
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_MODEL_SIGNAL_CUSTOMER_NOTIFICATION,
            $signal->getEmail(),
            null,
            $emailVars
        );
    }

    
}