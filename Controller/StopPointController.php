<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StopPointController extends Controller
{
    public function listAction($coverage_id, $network_id, $line_id, $route_id)
    {
        $navitia = $this->get('iussaad_navitia');
        $routes = $navitia->getStopPoints($coverage_id, $network_id, $line_id, $route_id);
        
        $line = $this->getDoctrine()->getRepository(
            'CanalTPMethBundle:Line',
            'meth'
            )->findOneBy(
                array(
                    'coverageId'    => $coverage_id,
                    'networkId'     => $network_id,
                    'navitiaId'     => $line_id,
                )
            );
            
        $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
        $routes->route_schedules[0]->table->rows = $stopPointManager->enhanceStopPoints($routes->route_schedules[0]->table->rows, $line);

        return $this->render(
            'CanalTPMethBundle:StopPoint:list.html.twig',
            array(
                'line'          => $line,
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
