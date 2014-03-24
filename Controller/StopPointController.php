<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StopPointController extends Controller
{
    public function listAction($network_id, $line_id, $externalRouteId, $seasonId = null)
    {
        $navitia = $this->get('sam_navitia');
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');
        $network = $this->get('canal_tp_mtt.network_manager')->findOneByExternalId($network_id);
        $seasons = $this->get('canal_tp_mtt.season_manager')->findAllByNetworkId($network->getExternalId());
        $selectedSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $routes = $navitia->getStopPoints($network->getExternalCoverageId(), $network_id, $line_id, $externalRouteId);
        $lineConfig = $this->getDoctrine()->getRepository(
            'CanalTPMttBundle:LineConfig'
        )->findOneBy(array('externalLineId' => $line_id, 'season' => $selectedSeason));

        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        if (!empty($lineConfig)) {
            $routes->route_schedules[0]->table->rows = $stopPointManager->enhanceStopPoints(
                $routes->route_schedules[0]->table->rows,
                $timetableManager->findTimetableByExternalRouteIdAndLineConfig($externalRouteId, $lineConfig)
            );
        }

        return $this->render(
            'CanalTPMttBundle:StopPoint:list.html.twig',
            array(
                'lineConfig'        => $lineConfig,
                'routes'            => $routes,
                'current_route'     => $externalRouteId,
                'externalNetworkId' => $network->getExternalId(),
                'externalLineId'    => $line_id,
                'seasons'           => $seasons,
                'selectedSeason'    => $selectedSeason,
                'externalRouteId'   => $externalRouteId,
            )
        );
    }
}
