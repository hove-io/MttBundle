<?php

namespace CanalTP\MttBundle\Controller;

use \Symfony\Component\HttpFoundation\JsonResponse;

class StopPointController extends AbstractController
{
    public function listAction($externalNetworkId, $externalLineId = false, $externalRouteId = false, $seasonId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_STOP_TIMETABLE');

        $navitia = $this->get('canal_tp_mtt.navitia');
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter($perimeter);
        $currentSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);
        if (empty($externalLineId)) {
            list($externalLineId, $externalRouteId) = $navitia->getFirstLineAndRouteOfNetwork(
                $perimeter->getExternalCoverageId(),
                $externalNetworkId
            );
        }
        $routes = $navitia->getStopPoints(
            $perimeter->getExternalCoverageId(),
            $externalNetworkId,
            $externalLineId,
            $externalRouteId
        );
        $lineConfig = $this->getDoctrine()->getRepository(
            'CanalTPMttBundle:LineConfig'
        )->findOneBy(array('externalLineId' => $externalLineId, 'season' => $currentSeason));

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
                'pageTitle'         => "menu.edit_timetables",
                'lineConfig'        => $lineConfig,
                'routes'            => $routes,
                'externalNetworkId' => $perimeter->getExternalNetworkId(),
                'externalLineId'    => $externalLineId,
                'externalRouteId'   => $externalRouteId,
                'seasons'           => $seasons,
                'currentSeason'     => $currentSeason
            )
        );
    }

    public function jsonListAction($externalNetworkId, $lineId, $externalRouteId)
    {
        $navitia = $this->get('canal_tp_mtt.navitia');
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $response = $navitia->getStopPoints(
            $perimeter->getExternalCoverageId(),
            $externalNetworkId,
            $lineId,
            $externalRouteId
        );
        $stops = array();

        foreach ($response->route_schedules[0]->table->rows as $row) {
            $stops[$row->stop_point->id] = array('name' => $row->stop_point->name);
        }

        return new JsonResponse(array('stops' => $stops));
    }
}
