<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StopPointController extends Controller
{
    public function listAction($coverage_id, $network_id, $line_id, $route_id)
    {
        $navitia = $this->get('iussaad_navitia');
        $routes = $navitia->getStopPoints($coverage_id, $network_id, $line_id, $route_id);
        
        return $this->render(
            'CanalTPMethBundle:StopPoint:list.html.twig',
            array(
                'routes'        => $routes,
                'current_route' => $route_id,
                'coverage_id'   => $coverage_id,
                'network_id'    => $network_id,
                'line_id'       => $line_id,
                'route_id'      => $route_id,
            )
        );
    }
}
