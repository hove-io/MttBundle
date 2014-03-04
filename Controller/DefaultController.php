<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $mtt_user = $this->get('canal_tp_mtt.user');

        return $this->render(
            'CanalTPMttBundle:Default:index.html.twig',
            array('networks' => $mtt_user->getNetworks())
        );
    }

    public function navigationAction($current_route = null)
    {
        $meth_navitia = $this->get('canal_tp_meth.navitia');
        $mtt_user = $this->get('canal_tp_mtt.user');

        $networks = $mtt_user->getNetworks();
        // TODO: get current network
        $result = $meth_navitia->getLinesByMode(
            $networks[0]['external_coverage_id'],
            $networks[0]['external_id']
        );

        return $this->render(
            'CanalTPMttBundle:Default:navigation.html.twig',
            array(
                'result' => $result,
                'coverageId' => $networks[0]['external_coverage_id'],
                'current_route' => $current_route
            )
        );
    }
}
