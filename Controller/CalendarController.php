<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * CalendarController
 */
class CalendarController extends Controller
{
    public function viewAction($externalCoverageId, $externalRouteId, $externalStopPointId)
    {
        $calendarManager = $this->get('canal_tp_meth.calendar_manager');
        $timetableManager = $this->get('canal_tp_meth.timetable_manager');
        $timetable = $timetableManager->getTimetable($externalRouteId, $externalCoverageId);
        $stopPoint = $this->get('canal_tp_meth.stop_point_manager')->getStopPoint(
            $externalStopPointId, 
            $externalCoverageId
        );
        $calendarsAndNotes = $calendarManager->getCalendars($externalCoverageId, $timetable, $stopPoint);

        return $this->render(
            'CanalTPMethBundle:Calendar:view.html.twig',
            array(
                'calendars'     => $calendarsAndNotes['calendars'],
                'notes'         => $calendarsAndNotes['notes'],
                'current_route' => $externalRouteId,
            )
        );
    }
}