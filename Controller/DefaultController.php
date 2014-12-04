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
        $perimeterManager = $this->get('canal_tp_mtt.perimeter_manager');
        $userManager = $this->get('canal_tp_mtt.user');

        // TODO: Put the current or default Network of User.
        $networks = $userManager->getNetworks();

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('externalNetworkId', $externalNetworkId));

        $currentPerimeter =
            $externalNetworkId == null ?
            $networks->first() :
            $networks->matching($criteria)->first()
        ;

        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array(
                'tasks'             => $perimeterManager->getLastTasks($currentPerimeter),
                'externalNetworkId' => $currentPerimeter->getExternalNetworkId()
            )
        );
    }

    public function navigationAction($externalNetworkId, $current_season, $current_route = null)
    {
        // TODO: Put the default Network of User. (for $externalNetworkId)
        $mtt_navitia = $this->get('canal_tp_mtt.navitia');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        try {
            $result = $mtt_navitia->findAllLinesByMode(
                $perimeter->getExternalCoverageId(),
                $perimeter->getExternalNetworkId()
            );
        } catch (\Exception $e) {
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
                'coverageId'        => $perimeter->getExternalCoverageId(),
                'current_route'     => $current_route,
                'current_season'    => $current_season
            )
        );
    }
}
