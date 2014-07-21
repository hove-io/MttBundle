<?php

namespace CanalTP\MttBundle\Controller;

class AreaController extends AbstractController
{
    public function listAction($externalNetworkId)
    {
        $this->isGranted('BUSINESS_LIST_AREA');
        return $this->render(
            'CanalTPMttBundle:Area:list.html.twig',
            array(
                'areas' => $this->get('canal_tp_mtt.area_manager')->findAll(),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }
}
