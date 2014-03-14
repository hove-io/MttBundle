<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $mtt_user = $this->get('canal_tp_mtt.user');

        // TODO: Put the current or default Network of User.
        $network = $mtt_user->getNetworks();
        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array(
                'network'           => $network[0],
                'externalNetworkId' => $network[0]['external_id']
            )
        );
    }

    public function navigationAction($externalNetworkId = 'network:Filbleu', $current_route = null)
    {
        $meth_navitia = $this->get('canal_tp_mtt.navitia');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);

        $result = $meth_navitia->getLinesByMode(
            $network->getExternalCoverageId(),
            $network->getExternalId()
        );

        return $this->render(
            'CanalTPMttBundle:Default:navigation.html.twig',
            array(
                'result' => $result,
                'coverageId' => $network->getExternalCoverageId(),
                'current_route' => $current_route
            )
        );
    }
}
