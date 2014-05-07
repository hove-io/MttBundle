<?php

/**
 * FormListener
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class FormListener
{
    private $newResponse = null;

    private function initData($response)
    {
        if ($response->getStatusCode() == Response::HTTP_FOUND) {
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

    private function isJsonResponse(FilterResponseEvent $event)
    {
        return $event->getResponse() instanceof JsonResponse;
    }

    private function isAjaxRequestForm(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        return (
            $request->isXmlHttpRequest() && $request->getMethod() == 'POST' &&
            ($response->getStatusCode() == Response::HTTP_FOUND || $response->getStatusCode() == Response::HTTP_OK)
        );
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->isAjaxRequestForm($event) && $this->isJsonResponse($event) != true) {
            $this->newResponse = new JsonResponse();

            $this->initData($event->getResponse());
            $event->setResponse($this->newResponse);
        }
    }
}
