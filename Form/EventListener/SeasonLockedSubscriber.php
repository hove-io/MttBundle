<?php

namespace CanalTP\MttBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;

class SeasonLockedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'submit',
            FormEvents::POST_BIND => 'submit'
        );
    }

    public function submit(FormEvent $event)
    {
        $entity = $event->getData();

        if (!empty($entity) && $entity->isLocked()) {
            $event->getForm()->addError(new FormError('error.element_locked'));
        }
    }
}
