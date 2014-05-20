<?php

namespace CanalTP\MttBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SeasonLockedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Dit au dispatcher que vous voulez écouter l'évènement
        // form.pre_set_data et que la méthode preSetData doit être appelée
        return array(FormEvents::SUBMIT => 'submit');
    }

    public function submit(FormEvent $event)
    {
        $entity = $event->getData();
        $form = $event->getForm();

        var_dump(get_class($entity));
        die;
    }
}