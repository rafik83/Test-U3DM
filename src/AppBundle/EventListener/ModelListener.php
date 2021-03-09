<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Model;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use AppBundle\Event\ModelEvent;
use AppBundle\Event\ModelEvents;
use AppBundle\Service\SendinBlue;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;

class ModelListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    private $router;
    private $sendinBlue;

    /**
     * ModelListener constructor
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
            FormEvents::SUBMIT => 'handleModelForm',
            ModelEvents::POST_ADMIN_SENT_TO_CORRECTION => 'sendCorrectionNotification',
            ModelEvents::POST_ADMIN_SENT_TO_DELETE => 'sendDeleteNotification'
        );
    }

    /**
     * Handle the model form
     *
     * @param FormEvent $event
     */
    public function handleModelForm(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Model $model */
        $model = $event->getData();

        // make sure some fields are not negative
        if ($model->getPriceTaxExcl() == null) {
            $model->setPriceTaxExcl(0);
        }
        if (0 > $model->getPriceTaxExcl()) {
            $form->addError(new FormError($this->translator->trans('model.negative_value', array('%field%' => $this->translator->trans('model.form.label.price_tax_excl')), 'validators')));
        }
        
        $fileName = $model->getAttachmentFile();
        $size = (filesize($fileName));
        
        if($size < 1 && $fileName != null) {
            $form->addError(new FormError($this->translator->trans('model.size_file',  array('%field%' => ini_get('upload_max_filesize')), 'validators')));
        }
        $findme   = '/';
        $pos = strpos($model->getName(), $findme);

        if ($pos > -1 ) {
            $newName = str_replace("/", "", $model->getName());
            $model->setName($newName);
            $form->addError(new FormError($this->translator->trans('model.name',  array('%field%' => $this->translator->trans('model.form.label.name')), 'validators')));
        }
        
        if ($fileName != null) {
            $extension = $fileName->getClientOriginalExtension();
            echo("<script>console.log('PHP: ".$extension."');</script>");
            if ($extension != "zip" && $extension != "ZIP" && 
                $extension != "stl" && $extension != "STL" && 
                $extension != "obj" && $extension != "OBJ") {
                $form->addError(new FormError($this->translator->trans('model.type_file',  array('%field%' => $this->translator->trans('model.form.label.attachmentFile')), 'validators')));
            }
        }

        
    }

    /**
     * Send maker e-mail notification when admin sends quotation to correction
     *
     * @param ModelEvent $event
     */
    public function sendCorrectionNotification(ModelEvent $event)
    {
        $model = $event->getModel();

        // email vars
        $emailVars = array(
            'ModeleNom'         => $model->getName(),
            'MakerName'         => $model->getMaker()->getCompany(),
            'DescModerateur'    => $model->getCorrectionReason(),
            'accountUrl'        => $this->router->generate('user_maker_model_modify', array('id' => $model->getId()), $this->router::ABSOLUTE_URL)
        );

        // send maker e-mail
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_MODEL_CORRECTION_MESSAGE_NOTIFICATION,
            $model->getMaker()->getUser()->getEmail(),
            $model->getMaker()->getFullname(),
            $emailVars
        );
    }

    /**
     * Send maker e-mail notification when admin sends quotation to correction
     *
     * @param ModelEvent $event
     */
    public function sendDeleteNotification(ModelEvent $event)
    {
        $model = $event->getModel();

        // email vars
        $emailVars = array(
            'ModeleNom'         => $model->getName(),
            'MakerName'         => $model->getMaker()->getCompany(),
            'DescModerateur'    => $model->getCorrectionReason(),
            'accountUrl'        => $this->router->generate('user_maker_creations', array(), $this->router::ABSOLUTE_URL)
        );

        // send maker e-mail
        $this->sendinBlue->sendTransactional(
            SendinBlue::TEMPLATE_ID_MODEL_DELETE_MESSAGE_NOTIFICATION,
            $model->getMaker()->getUser()->getEmail(),
            $model->getMaker()->getFullname(),
            $emailVars
        );
    }
}