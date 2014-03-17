<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * CalendarController
 */
class CalendarController extends Controller
{
    public function viewAction($externalNetworkId, $externalRouteId, $externalStopPointId)
    {
        $calendarManager = $this->get('canal_tp_mtt.calendar_manager');
        $networkManager = $this->get('canal_tp_mtt.network_manager');

        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $calendarsAndNotes = $calendarManager->getCalendarsForStopPoint(
            $network->getExternalCoverageId(),
            $externalRouteId, 
            $externalStopPointId
        );

        return $this->render(
            'CanalTPMttBundle:Calendar:view.html.twig',
            array(
                'calendars'     => $calendarsAndNotes['calendars'],
                'notes'         => $calendarsAndNotes['notes'],
                'current_route' => $externalRouteId,
            )
        );
    }
}