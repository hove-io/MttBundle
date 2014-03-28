<?php

namespace CanalTP\MttBundle\Controller;

class NetworkController extends AbstractController
{
    public function indexAction()
    {
        return $this->render(
            'CanalTPMttBundle:Network:index.html.twig'
        );
    }
}
