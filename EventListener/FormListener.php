<?php

/**
 * FormListener
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class FormListener
{
    private $newResponse = null;

    private function initData($response)
    {
        if ($response->getStatusCode() == 302) {
            $this->newResponse->setData(array(
                'status' => true,
                'location' => $response->headers->get('location')
            ));
        } else {
            $this->newResponse->setData(array(
                'status' => false,
                'content' => $response->getContent()
            ));
            $this->newResponse->setStatusCode($response->getStatusCode());
        }
    }

    private function isAjaxRequestForm(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        return (
            $request->isXmlHttpRequest() && $request->getMethod() == 'POST' &&
            ($response->getStatusCode() == 302 || $response->getStatusCode() == 200)
        );
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->isAjaxRequestForm($event)) {
            $this->newResponse = new JsonResponse();

            $this->initData($event->getResponse());
            $event->setResponse($this->newResponse);
        }
    }
}
