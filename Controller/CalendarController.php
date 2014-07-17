<?php

namespace CanalTP\MttBundle\Controller;

/*
 * CalendarController
 */
class CalendarController extends AbstractController
{
    public function viewAction($externalNetworkId, $externalRouteId, $externalStopPointId, $currentSeasonId)
    {
        $calendarManager = $this->get('canal_tp_mtt.calendar_manager');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');

        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $calendars = $calendarManager->getCalendarsForStopPoint(
            $network->getExternalCoverageId(),
            $externalRouteId,
            $externalStopPointId
        );

        $prevNextStopPoints = $stopPointManager->getPrevNextStopPoints(
            $network,
            $externalRouteId,
            $externalStopPointId
        );

        $currentSeason = $this->get('canal_tp_mtt.season_manager')->find($currentSeasonId);

        return $this->render(
            'CanalTPMttBundle:Calendar:view.html.twig',
            array(
                'pageTitle'         => $this->get('translator')->trans(
                    'calendar.view_title',
                    array(),
                    'default'
                ),
                'currentNetwork'    => $network,
                'externalNetworkId' => $externalNetworkId,
                'externalStopPointId' => $externalStopPointId,
                'calendars'         => $calendars,
                'current_route'     => $externalRouteId,
                'currentSeason'     => $currentSeason,
                'prevNextStopPoints'=> $prevNextStopPoints,
            )
        );
    }
}
