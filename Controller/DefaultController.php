<?php

namespace CanalTP\MttBundle\Controller;

class DefaultController extends AbstractController
{
    private function findNetwork($externalNetworkId, $networks)
    {
        foreach ($networks as $network) {
            if ($network->getExternalNetworkId() == $externalNetworkId) {
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
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $mtt_user = $this->get('canal_tp_mtt.user');

        // TODO: Put the current or default Network of User.
        $networks = $mtt_user->getNetworks(
            $this->get('security.context')->getToken()->getUser()
        );
        $currentNetwork =
            $externalNetworkId == null ?
            $networks[0] :
            $this->findNetwork($externalNetworkId, $networks);
        // make currentNetwok a doctrine object
        $currentNetwork = $networkManager->find($currentNetwork);

        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array(
                'currentNetwork'    => $currentNetwork,
                'tasks'             => $networkManager->getLastTasks($currentNetwork),
                'externalNetworkId' => $currentNetwork->getExternalId()
            )
        );
    }

    public function navigationAction($externalNetworkId, $current_season, $current_route = null)
    {
        // TODO: Put the default Network of User. (for $externalNetworkId)
        $mtt_navitia = $this->get('canal_tp_mtt.navitia');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        try {
            $result = $mtt_navitia->findAllLinesByMode(
                $network->getExternalCoverageId(),
                $network->getExternalId()
            );
        } catch(\Exception $e) {
            $errorMessage = $e->getMessage();
            $result = array();
            $this->get('session')->getFlashBag()->add(
                'danger',
                $errorMessage
            );
        }
        return $this->render(
            'CanalTPMttBundle:Default:navigation.html.twig',
            array(
                'result'            => $result,
                'coverageId'        => $network->getExternalCoverageId(),
                'current_route'     => $current_route,
                'current_season'    => $current_season
            )
        );
    }
}
