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
                $timetable->getLineConfig()->getSeason()->getPerimeter(),
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

    private function renderLayout($timetable, $externalStopPointId, $editable = true, $displayMenu = true, $stopPointId = null)
    {
        $externalCoverageId = $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalCoverageId();

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
                'notesType'             => $timetable->getLineConfig()->getLayoutConfig()->getNotesType(),
                'orientation'           => $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getOrientationAsString(),
                'currentNetwork'        => $timetable->getLineConfig()->getSeason()->getPerimeter(),
                'externalNetworkId'     => $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
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
                'layout'                => $timetable->getLineConfig()->getLayoutConfig(),
                'editable'              => $editable,
                'displayMenu'           => $displayMenu,
                'templatePath'          => '@CanalTPMtt/Layouts/uploads/' . $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/',
                'imgPath'               => 'bundles/canaltpmtt/img/uploads/' . $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/',
                'cssPath'               => 'bundles/canaltpmtt/css/uploads/' . $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/',
                'externalStopPointId'   => $stopPointId
            )
        );
    }

    /*
     * Display a layout and make it editable via javascript
     */
    public function editAction($externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $this->isGranted('BUSINESS_EDIT_LAYOUT');
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $stopPointId = $this->get('canal_tp_mtt.navitia')->getStopPoints(
            $perimeter->getExternalCoverageId(),
            $perimeter->getExternalNetworkId(),
            $externalLineId,
            $externalRouteId
        )->route_schedules[0]->table->rows[0]->stop_point->id;
        $timetable = $this->getTimetable(
            $externalRouteId,
            $perimeter->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );

        return $this->renderLayout($timetable, $externalStopPointId, true, true, $stopPointId);
    }

    /*
     * Display a layout
     * This action needs to be accessible by an anonymous user
     */
    public function viewAction($externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $customerId = $this->getRequest()->get('customerId');

        if ($customerId == null) {
            $customer = $this->getUser()->getCustomer();
        } else {
            $customer = $this->get('sam_core.customer')->find($customerId);
        }
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );
        $timetable = $this->getTimetable(
            $externalRouteId,
            $perimeter->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );

        $displayMenu = $this->get('security.context')->getToken()->getUser() != 'anon.';
        if ($displayMenu) {
            $displayMenu = $this->get('request')->get('timetableOnly', false) != true;
        }

        return $this->renderLayout($timetable, $externalStopPointId, false, $displayMenu);
    }
}
