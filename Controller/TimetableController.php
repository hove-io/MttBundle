<?php

namespace CanalTP\MttBundle\Controller;

class TimetableController extends AbstractController
{
    private $mediaManager;

    /**
     * @function retrieve a timetable entity
     */
    private function getTimetable($routeExternalId, $externalCoverageId, $lineConfig)
    {
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');

        return $timetableManager->getTimetable($routeExternalId, $externalCoverageId, $lineConfig);
    }

    private function getStopPoint($externalStopPointId, $timetable, $externalCoverageId)
    {
        // are we on a specific stop_point
        if ($externalStopPointId != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint(
                $externalStopPointId,
                $timetable,
                $externalCoverageId
            );
            $prevNextStopPoints = $stopPointManager->getPrevNextStopPoints(
                $timetable->getLineConfig()->getSeason()->getNetwork(),
                $timetable->getExternalRouteId(),
                $externalStopPointId
            );
        // route level
        } else {
            $stopPointLevel = false;
            $stopPointInstance = false;
            $prevNextStopPoints = false;
        }

        return array(
            'stopPointLevel'    => $stopPointLevel,
            'stopPointInstance' => $stopPointInstance,
            'prevNextStopPoints'=> $prevNextStopPoints,
        );
    }

    private function renderLayout($timetable, $externalStopPointId, $editable = true, $displayMenu = true)
    {
        $externalCoverageId = $timetable->getLineConfig()->getSeason()->getNetwork()->getExternalCoverageId();

        $stopPointData = $this->getStopPoint(
            $externalStopPointId,
            $timetable,
            $externalCoverageId
        );

        if (!empty($stopPointData['stopPointInstance'])) {
            $calendarsAndNotes = $this->get('canal_tp_mtt.calendar_manager')->getCalendars(
                $externalCoverageId,
                $timetable,
                $stopPointData['stopPointInstance']
            );
        } else {
            $calendarsAndNotes = array('calendars'=>'', 'notes'=> '');
        }
        $this->addFlashIfSeasonLocked($timetable->getLineConfig()->getSeason());
        return $this->render(
            'CanalTPMttBundle:Layouts:' . $timetable->getLineConfig()->getTwigPath(),
            array(
                'pageTitle'             => 'timetable.titles.' . ($editable ? 'edition' : 'preview'),
                'timetable'             => $timetable,
                'currentNetwork'        => $timetable->getLineConfig()->getSeason()->getNetwork(),
                'externalNetworkId'     => $timetable->getLineConfig()->getSeason()->getNetwork()->getExternalId(),
                'externalRouteId'       => $timetable->getExternalRouteId(),
                'externalCoverageId'    => $externalCoverageId,
                'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                'currentSeason'         => $timetable->getLineConfig()->getSeason(),
                'currentSeasonId'       => $timetable->getLineConfig()->getSeason()->getId(),
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'prevNextStopPoints'    => $stopPointData['prevNextStopPoints'],
                'calendars'             => $calendarsAndNotes['calendars'],
                'notes'                 => $calendarsAndNotes['notes'],
                'blockTypes'            => $this->container->getParameter('blocks'),
                'layout'                => $timetable->getLineConfig()->getLayout(),
                'editable'              => $editable,
                'displayMenu'           => $displayMenu
            )
        );
    }

    /*
     * Display a layout and make it editable via javascript
     */
    public function editAction($externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $this->isGranted('BUSINESS_EDIT_LAYOUT');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->getTimetable(
            $externalRouteId,
            $network->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );

        return $this->renderLayout($timetable, $externalStopPointId, true, true);
    }

    /*
     * Display a layout
     * This action needs to be accessible by an anonymous user
     */
    public function viewAction($externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->getTimetable(
            $externalRouteId,
            $network->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );
        $displayMenu = $this->get('security.context')->getToken()->getUser() != 'anon.'; 
        if ($displayMenu)
            $displayMenu = $this->get('request')->get('timetableOnly', false) != true;

        return $this->renderLayout($timetable, $externalStopPointId, false, $displayMenu);
    }
}
