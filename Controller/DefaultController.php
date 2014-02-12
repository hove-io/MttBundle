<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $networks = $this->getDoctrine()
        ->getRepository('CanalTPMethBundle:Network', 'mtt')
        ->findNetworksByUserId($this->getUser()->getId());

        return $this->render(
            'CanalTPMethBundle:Default:index.html.twig',
            array('networks' => $networks)
        );
    }

    public function navigationAction($current_route = null)
    {
        $meth_navitia = $this->get('canal_tp_meth.navitia');
        $networks = $this->getDoctrine()
            ->getRepository('CanalTPMethBundle:Network', 'mtt')
            ->findNetworksByUserId($this->getUser()->getId());
        // Configuration
        if (count($networks) > 0)
        {
            $result = $meth_navitia->getLinesByMode(
                $networks[0]['external_coverage_id'],
                $networks[0]['external_id']
            );
        }
        else
        {
            throw new \Exception($this->get('translator')->trans('controller.default.navigation.no_networks', array(), 'exceptions'));
        }

        return $this->render(
            'CanalTPMethBundle:Default:navigation.html.twig',
            array(
                'result' => $result,
                'coverageId' => $networks[0]['external_coverage_id'],
                'current_route' => $current_route
            )
        );
    }
}
