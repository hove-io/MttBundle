<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractController extends Controller
{
    protected function isGranted($businessId)
    {
        if ($this->get('security.context')->isGranted($businessId) === false) {
            throw new AccessDeniedException();
        }
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
}
