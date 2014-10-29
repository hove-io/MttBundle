<?php

namespace CanalTP\MttBundle\Controller;

use Doctrine\Common\Collections\Criteria;

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
        $userManager = $this->get('canal_tp_mtt.user');

        // TODO: Put the current or default Network of User.
        $networks = $userManager->getNetworks();
//        $networks = $mttUser->getNetworks(
//            $this->get('security.context')->getToken()->getUser()
//        );

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('externalNetworkId', $externalNetworkId));

        $currentNetwork =
            $externalNetworkId == null ?
            $networks->first() :
            $networks->matching($criteria)
        ;
//        // make currentNetwok a doctrine object
//        $currentNetwork = $networkManager->find($currentNetwork);

        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array(
                'currentNetwork'    => $currentNetwork,
                'tasks'             => $networkManager->getLastTasks($currentNetwork),
                'externalNetworkId' => $currentNetwork->getExternalNetworkId()
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
                $network->getExternalNetworkId()
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
