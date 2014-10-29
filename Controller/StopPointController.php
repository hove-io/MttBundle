<?php

namespace CanalTP\MttBundle\Controller;

use \Symfony\Component\HttpFoundation\JsonResponse;

class StopPointController extends AbstractController
{
    public function listAction($externalNetworkId, $line_id = false, $externalRouteId = false, $seasonId = null)
    {
        $navitia = $this->get('canal_tp_mtt.navitia');
        $network = $this->get('canal_tp_mtt.network_manager')->findOneByExternalId($externalNetworkId);
        $seasons = $this->get('canal_tp_mtt.season_manager')->findAllByNetworkId($network->getExternalNetworkId());
        $currentSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);
        if (empty($line_id)) {
            list($line_id, $externalRouteId) = $navitia->getFirstLineAndRouteOfNetwork(
                $network->getExternalCoverageId(),
                $externalNetworkId
            );
        }
        $routes = $navitia->getStopPoints(
            $network->getExternalCoverageId(),
            $externalNetworkId,
            $line_id,
            $externalRouteId
        );
        $lineConfig = $this->getDoctrine()->getRepository(
            'CanalTPMttBundle:LineConfig'
        )->findOneBy(array('externalLineId' => $line_id, 'season' => $currentSeason));

        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        if (!empty($lineConfig)) {
            $timetableManager = $this->get('canal_tp_mtt.timetable_manager');
            $timetable = $timetableManager->findTimetableByExternalRouteIdAndLineConfig($externalRouteId, $lineConfig);
            if (!empty($timetable)) {
                $routes->route_schedules[0]->table->rows = $stopPointManager->enhanceStopPoints(
                    $routes->route_schedules[0]->table->rows,
                    $timetable
                );
            }
        }
        $currentSeasonId = empty($currentSeason) ? false : $currentSeason->getId();

        return $this->render(
            'CanalTPMttBundle:StopPoint:list.html.twig',
            array(
                'pageTitle'         => "menu.edit_timetables",
                'lineConfig'        => $lineConfig,
                'routes'            => $routes,
                'current_route'     => $externalRouteId,
                'currentNetwork'    => $network,
                'externalNetworkId' => $network->getExternalNetworkId(),
                'externalLineId'    => $line_id,
                'seasons'           => $seasons,
                'currentSeason'     => $currentSeason,
                'currentSeasonId'   => $currentSeasonId,
                'externalRouteId'   => $externalRouteId,
            )
        );
    }

    public function jsonListAction($externalNetworkId, $lineId, $externalRouteId)
    {
        $navitia = $this->get('canal_tp_mtt.navitia');
        $network = $this->get('canal_tp_mtt.network_manager')->findOneByExternalId($externalNetworkId);

        $response = $navitia->getStopPointsByRoute(
            $network->getExternalCoverageId(),
            $externalNetworkId,
            $externalRouteId
        );
        $stops = array();
        foreach ($response->stop_points as $stop) {
            $stops[$stop->id] = array('name' => $stop->name);
        }

        return new JsonResponse(array('stops' => $stops));
    }
}
