<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StopPointController extends Controller
{
    public function listAction($coverage_id, $network_id, $line_id, $externalRouteId)
    {
        $navitia = $this->get('iussaad_navitia');
        $routes = $navitia->getStopPoints($coverage_id, $network_id, $line_id, $externalRouteId);

        $line = $this->getDoctrine()->getRepository(
            'CanalTPMttBundle:Line',
            'mtt'
        )->findOneBy(array('externalId' => $line_id));

        $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
        $routes->route_schedules[0]->table->rows = $stopPointManager->enhanceStopPoints($routes->route_schedules[0]->table->rows, $line);

        return $this->render(
            'CanalTPMttBundle:StopPoint:list.html.twig',
            array(
                'line'              => $line,
                'routes'            => $routes,
                'current_route'     => $externalRouteId,
                'network_id'        => $network_id,
                'line_id'           => $line_id,
                'externalCoverageId'=> $coverage_id,
                'externalRouteId'   => $externalRouteId,
            )
        );
    }
}
