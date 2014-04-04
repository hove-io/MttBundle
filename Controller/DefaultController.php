<?php

namespace CanalTP\MttBundle\Controller;

class DefaultController extends AbstractController
{
    public function indexAction()
    {
        $mtt_user = $this->get('canal_tp_mtt.user');

        // TODO: Put the current or default Network of User.
        $network = $mtt_user->getNetworks(
            $this->get('security.context')->getToken()->getUser()
        );
        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array(
                'network'           => $network[0],
                'externalNetworkId' => $network[0]['external_id']
            )
        );
    }

    public function navigationAction($externalNetworkId = 'network:Filbleu', $current_season, $current_route = null)
    {
        // TODO: Put the current or default Network of User. (for $externalNetworkId)
        $mtt_navitia = $this->get('canal_tp_mtt.navitia');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);

        $result = $mtt_navitia->getLinesByMode(
            $network->getExternalCoverageId(),
            $network->getExternalId()
        );

        return $this->render(
            'CanalTPMttBundle:Default:navigation.html.twig',
            array(
                'result' => $result,
                'coverageId' => $network->getExternalCoverageId(),
                'current_route' => $current_route,
                'current_season' => $current_season
            )
        );
    }
}
