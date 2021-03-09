<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ModelComments;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use AppBundle\Event\ModelCommentsEvent;
use AppBundle\Event\ModelCommentsEvents;
use AppBundle\Service\SendinBlue;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;

class ModelCommentsListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    private $router;
    private $sendinBlue;

    /**
     * ModelCommentsListener constructor
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
            ModelCommentsEvents::POST_ADMIN_SENT_TO_COMMENT => 'sendMakerNotificationForModelComments',
        );
    }

    /**
     * Send maker e-mail notification when admin sends quotation to correction
     *
     * @param ModelCommentsEvent $event
     */
    public function sendMakerNotificationForModelComments(ModelCommentsEvent $event)
    {
        $modelComments = $event->getModelComments();

        // email vars
        $emailVars = array(
            'ModeleNom'         => $modelComments->getModel()->getName(),
            'MakerNom'          => $modelComments->getModel()->getMaker()->getCompany(),
            'NomInternaute'     => $modelComments->getCustomer()->getFirstname(),
            'DescCommentaire'   => $modelComments->getDescription(),
            'accountUrl'        => $this->router->generate('model_detail', array('id' => $modelComments->getModel()->getId(), 'name' => $modelComments->getModel()->getName()), $this->router::ABSOLUTE_URL)
        );

        // send maker e-mail
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_MODEL_COMMENT_VALID_NOTIFICATION,
            $modelComments->getModel()->getMaker()->getUser()->getEmail(),
            $modelComments->getModel()->getMaker()->getFullname(),
            $emailVars
        );
    }


    
}