<?php

namespace CanalTP\MttBundle\Controller;

/*
 * LayoutController
 */
class LayoutController extends AbstractController
{
    public function indexAction($externalNetworkId)
    {
        $network = $this->get('canal_tp_mtt.network_manager')->findOneByExternalId($externalNetworkId);

        return $this->render(
            'CanalTPMttBundle:Layout:list.html.twig',
            array(
                'pageTitle' => 'menu.layouts_manage',
                'layoutConfigs' => $network->getLayoutConfigs(),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }
}
