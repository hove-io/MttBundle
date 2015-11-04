<?php

namespace CanalTP\MttBundle\Controller;

use Doctrine\Common\Collections\Criteria;

class DefaultController extends AbstractController
{
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

    public function navigationAction($externalNetworkId, $seasonId, $options = array())
    {
        // TODO: Put the default Network of User. (for $externalNetworkId)
        $mttNavitia = $this->get('canal_tp_mtt.navitia');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        try {
            $result = $mttNavitia->findAllLinesByMode(
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
                'seasonId'          => $seasonId,
                'options'           => $options,
                'navigationMode'    => isset($options['navigationMode']) ? $options['navigationMode'] : 'routes'
            )
        );
    }
}
