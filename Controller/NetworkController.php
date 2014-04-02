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

    public function listAction()
    {
        $this->networkManager = $this->get('canal_tp_mtt.network_manager');

        return $this->render(
            'CanalTPMttBundle:Network:list.html.twig',
            array(
                'no_left_menu' => true,
                'networks' => $this->networkManager->findAll()
            )
        );
    }
}
