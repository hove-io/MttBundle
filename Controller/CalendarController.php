<?php

namespace CanalTP\MttBundle\Controller;

/*
 * CalendarController
 */
use CanalTP\MttBundle\Entity\Calendar;
use CanalTP\MttBundle\Form\Type\CalendarType;

class CalendarController extends AbstractController
{

    public function createAction()
    {
        $calendar = new Calendar();

        $form = $this->createForm(new CalendarType(), $calendar);
        $form->add('submit', 'submit', ['label' => 'global.validate', 'translation_domain' => 'messages']);

        return $this->render('CanalTPMttBundle:Calendar:create.html.twig', ['form' => $form->createView()]);
    }

    public function viewAction($externalNetworkId, $externalRouteId, $externalStopPointId, $currentSeasonId)
    {
        $calendarManager = $this->get('canal_tp_mtt.calendar_manager');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');

        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $calendars = $calendarManager->getCalendarsForStopPoint(
            $perimeter->getExternalCoverageId(),
            $externalRouteId,
            $externalStopPointId
        );

        $prevNextStopPoints = $stopPointManager->getPrevNextStopPoints(
            $perimeter,
            $externalRouteId,
            $externalStopPointId
        );

        $currentSeason = $this->get('canal_tp_mtt.season_manager')->find($currentSeasonId);

        return $this->render(
            'CanalTPMttBundle:Calendar:view.html.twig',
            array(
                'pageTitle'           => $this->get('translator')->trans(
                    'calendar.view_title',
                    array(),
                    'default'
                ),
                'externalNetworkId'   => $externalNetworkId,
                'externalStopPointId' => $externalStopPointId,
                'calendars'           => $calendars,
                'current_route'       => $externalRouteId,
                'currentSeason'       => $currentSeason,
                'prevNextStopPoints'  => $prevNextStopPoints,
            )
        );
    }
}
