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

        return $this->render(
            'CanalTPMttBundle:Calendar:view.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'externalStopPointId' => $externalStopPointId,
                'calendars'         => $calendars,
                'current_route'     => $externalRouteId,
                'currentSeasonId'   => $currentSeasonId,
                'prevNextStopPoints'=> $prevNextStopPoints,
            )
        );
    }
}
