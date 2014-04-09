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
    private function initData($response)
    {
        if ($response->getStatusCode() == 302) {
            $data = array(
                'status' => true,
                'location' => $response->headers->get('location')
            );
        } else if ($response->getStatusCode() == 200) {
            $data = array(
                'status' => false,
                'content' => $response->getContent()
            );
        }
        return ($data);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {
            $newResponse = new JsonResponse();

            $newResponse->setData($this->initData($event->getResponse()));
            $event->setResponse($newResponse);
        }
    }
}
