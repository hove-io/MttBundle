<?php

namespace CanalTP\MttBundle\Controller;

class DefaultController extends AbstractController
{
    private function findNetwork($externalNetworkId, $networks)
    {
        foreach ($networks as $network) {
            if ($network['external_id'] == $externalNetworkId) {
                return $network;
            }
        }
        throw new \Exception(
            $this->get('translator')->trans(
                'controller.default.network_not_found_4_user',
                array(),
                'exceptions'
            )
        );

    }

    public function indexAction($externalNetworkId = null)
    {
        $mtt_user = $this->get('canal_tp_mtt.user');

        // TODO: Put the current or default Network of User.
        $networks = $mtt_user->getNetworks(
            $this->get('security.context')->getToken()->getUser()
        );
        $currentNetwork = $externalNetworkId == null ? $networks[0] : $this->findNetwork($externalNetworkId, $networks);

        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array(
                'network'           => $currentNetwork,
                'externalNetworkId' => $currentNetwork['external_id']
            )
        );
    }

    public function navigationAction($externalNetworkId, $current_season, $current_route = null)
    {
        // TODO: Put the default Network of User. (for $externalNetworkId)
        $mtt_navitia = $this->get('canal_tp_mtt.navitia');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);

        $result = $mtt_navitia->findAllLinesByMode(
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
