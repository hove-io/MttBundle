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
                'layouts' => $network->getLayouts(),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }
}
