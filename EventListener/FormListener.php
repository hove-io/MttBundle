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

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {
            $this->newResponse = new JsonResponse();

            $this->initData($event->getResponse());
            $event->setResponse($this->newResponse);
        }
    }
}
