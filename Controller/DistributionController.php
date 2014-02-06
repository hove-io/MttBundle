<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DistributionController extends Controller
{
    public function listAction($coverageId, $networkId, $lineId, $routeId)
    {
        $navitia = $this->get('iussaad_navitia');
        $routes = $navitia->getStopPoints($coverageId, $networkId, $lineId, $routeId);

        $line = $this->getDoctrine()->getRepository(
            'CanalTPMethBundle:Line',
            'meth'
        )->findOneBy(
            array(
                'coverageId'    => $coverageId,
                'networkId'     => $networkId,
                'navitiaId'     => $lineId,
            )
        );

        return $this->render(
            'CanalTPMethBundle:Distribution:list.html.twig',
            array(
                'line'          => $line,
                'routes'        => $routes,
                'current_route' => $routeId,
                'coverage_id'   => $coverageId,
                'network_id'    => $networkId,
                'line_id'       => $lineId,
                'route_id'      => $routeId,
            )
        );
    }
}
