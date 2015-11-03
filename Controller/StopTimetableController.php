<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Entity\Template;

class StopTimetableController extends AbstractController
{
    private $mediaManager;

    /**
     * @function retrieve a stopTimetable entity
     */
    private function getStopTimetable($routeExternalId, $externalCoverageId, $lineConfig)
    {
        $stopTimetableManager = $this->get('canal_tp_mtt.stop_timetable_manager');

        return $stopTimetableManager->getStopTimetable($routeExternalId, $externalCoverageId, $lineConfig);
    }

    private function getStopPoint($externalStopPointId, $stopTimetable, $externalCoverageId)
    {
        // are we on a specific stop_point
        if ($externalStopPointId != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint(
                $externalStopPointId,
                $stopTimetable,
                $externalCoverageId
            );
            $prevNextStopPoints = $stopPointManager->getPrevNextStopPoints(
                $stopTimetable->getLineConfig()->getSeason()->getPerimeter(),
                $stopTimetable->getExternalRouteId(),
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

    /**
     * Render layout
     *
     * @param Request $request
     * @param StopTimetable $stopTimetable
     * @param string $externalStopPointId
     * @param boolean $editable = true
     * @param boolean $displayMenu = true
     * @param integer $stopPointId = null
     */
    private function renderLayout(Request $request, $stopTimetable, $externalStopPointId, $editable = true, $displayMenu = true, $stopPointId = null)
    {
        // Checking the associated Layout has a Template of type STOP_TYPE before rendering it
        if (!$stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getTemplate(Template::STOP_TYPE))
        {
            $this->addFlashMessage('danger', 'error.template.not_found', array('%type%' => Template::STOP_TYPE));
            return $this->redirect($request->headers->get('referer'));
        }

        $externalCoverageId = $stopTimetable->getLineConfig()->getSeason()->getPerimeter()->getExternalCoverageId();

        $stopPointData = $this->getStopPoint(
            $externalStopPointId,
            $stopTimetable,
            $externalCoverageId
        );

        if (!empty($stopPointData['stopPointInstance'])) {
            $calendarsAndNotes = $this->get('canal_tp_mtt.calendar_manager')->getCalendars(
                $externalCoverageId,
                $stopTimetable,
                $stopPointData['stopPointInstance']
            );
        } else {
            $calendarsAndNotes = array('calendars'=>'', 'notes'=> '');
        }
        $this->addFlashIfSeasonLocked($stopTimetable->getLineConfig()->getSeason());

        $layoutId = $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getId();
        $templateFile = $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getTemplate(Template::STOP_TYPE)->getPath();

        return $this->render(
            'CanalTPMttBundle:Layouts:' . $templateFile,
            array(
                'pageTitle'             => 'stop_timetable.titles.' . ($editable ? 'edition' : 'preview'),
                'stopTimetable'         => $stopTimetable,
                'notesType'             => $stopTimetable->getLineConfig()->getLayoutConfig()->getNotesType(),
                'orientation'           => $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getOrientationAsString(),
                'currentNetwork'        => $stopTimetable->getLineConfig()->getSeason()->getPerimeter(),
                'externalNetworkId'     => $stopTimetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'externalRouteId'       => $stopTimetable->getExternalRouteId(),
                'externalCoverageId'    => $externalCoverageId,
                'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                'currentSeason'         => $stopTimetable->getLineConfig()->getSeason(),
                'currentSeasonId'       => $stopTimetable->getLineConfig()->getSeason()->getId(),
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'prevNextStopPoints'    => $stopPointData['prevNextStopPoints'],
                'calendars'             => $calendarsAndNotes['calendars'],
                'notes'                 => $calendarsAndNotes['notes'],
                'blockTypes'            => $this->container->getParameter('blocks'),
                'layout'                => $stopTimetable->getLineConfig()->getLayoutConfig(),
                'editable'              => $editable,
                'displayMenu'           => $displayMenu,
                'templatePath'          => '@CanalTPMtt/Layouts/uploads/' . $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/',
                'imgPath'               => 'bundles/canaltpmtt/img/uploads/' . $layoutId . '/',
                'cssPath'               => 'bundles/canaltpmtt/css/uploads/' . $layoutId . '/',
                'externalStopPointId'   => $stopPointId
            )
        );
    }

    /*
     * Display a layout and make it editable via javascript
     */
    public function editAction(Request $request, $externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
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
        $stopTimetable = $this->getStopTimetable(
            $externalRouteId,
            $perimeter->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );

        return $this->renderLayout($request, $stopTimetable, $externalStopPointId, true, true, $stopPointId);
    }

    /*
     * Display a layout
     * This action needs to be accessible by an anonymous user
     */
    public function viewAction(Request $request, $externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
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
        $stopTimetable = $this->getStopTimetable(
            $externalRouteId,
            $perimeter->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );

        $displayMenu = $this->get('security.context')->getToken()->getUser() != 'anon.';
        if ($displayMenu) {
            $displayMenu = $this->get('request')->get('stopTimetableOnly', false) != true;
        }

        return $this->renderLayout($request, $stopTimetable, $externalStopPointId, false, $displayMenu);
    }
}
