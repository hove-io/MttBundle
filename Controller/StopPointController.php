<?php

namespace CanalTP\MttBundle\Controller;

class StopPointController extends AbstractController
{
    public function listAction($externalNetworkId, $line_id = false, $externalRouteId = false, $seasonId = null)
    {
        $navitia = $this->get('canal_tp_mtt.navitia');
        $network = $this->get('canal_tp_mtt.network_manager')->findOneByExternalId($externalNetworkId);
        $seasons = $this->get('canal_tp_mtt.season_manager')->findAllByNetworkId($network->getExternalId());
        $selectedSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($selectedSeason);
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
        )->findOneBy(array('externalLineId' => $line_id, 'season' => $selectedSeason));

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
                'currentSeasonId'   => $selectedSeason->getId(),
                'externalRouteId'   => $externalRouteId,
            )
        );
    }
}
