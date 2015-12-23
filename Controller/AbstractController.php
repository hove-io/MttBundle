<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractController extends Controller
{
    protected function isGranted($businessId, $object = null)
    {
        if ($this->get('security.authorization_checker')->isGranted($businessId, $object) === false) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Checking the request is POST ajax
     *
     * @param Request $request
     */
    protected function isPostAjax(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->isMethod('POST')))
            throw new AccessDeniedException();
    }

    protected function addFlashIfSeasonLocked($season)
    {
        $isLocked = (!empty($season) && $season->isLocked());

        if ($isLocked) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans(
                    'season.locked',
                    array(),
                    'default'
                )
            );
        }

        return $isLocked;
    }

    /**
     * Add flash message
     *
     * @param string $type
     * @param string $message
     * @param array $parameters
     */
    protected function addFlashMessage($type, $message, $parameters = array())
    {
        $this->addFlash(
            $type,
            $this->get('translator')->trans(
                $message,
                $parameters,
                'messages'
            )
        );
    }

    /**
     * Preparing a JsonResponse
     *
     * @param array $data
     * @param integer $statusCode
     */
    protected function prepareJsonResponse($data = array(), $statusCode = JsonResponse::HTTP_STATUS_OK)
    {
        $response = new JsonResponse();
        $response->setData($data);
        $response->setStatusCode($statusCode);

        return $response;
    }
}
