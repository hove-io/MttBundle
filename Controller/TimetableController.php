<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Entity\Template;

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

    /**
     * Render layout
     *
     * @param Request $request
     * @param Timetable $timetable
     * @param string $externalStopPointId
     * @param boolean $editable = true
     * @param boolean $displayMenu = true
     * @param integer $stopPointId = null
     */
    private function renderLayout(Request $request, $timetable, $externalStopPointId, $editable = true, $displayMenu = true, $stopPointId = null)
    {
        // Checking the associated Layout has a Template of type STOP_TYPE before rendering it
        if (!$timetable->getLineConfig()->getLayoutConfig()->getLayout()->getTemplate(Template::STOP_TYPE))
        {
            $this->addFlashMessage('danger', 'error.template.not_found', array('%type%' => Template::STOP_TYPE));
            return $this->redirect($request->headers->get('referer'));
        }

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

        $layoutId = $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getId();
        $templatePath = '@CanalTPMtt/Layouts/uploads/' . $layoutId . '/';
        $templateFile = $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getTemplate(Template::STOP_TYPE)->getPath();

        return $this->render(
            $templatePath . $templateFile,
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
                'templatePath'          => $templatePath,
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
        $timetable = $this->getTimetable(
            $externalRouteId,
            $perimeter->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );

        return $this->renderLayout($request, $timetable, $externalStopPointId, true, true, $stopPointId);
    }

    /*
     * Display a layout
     * This action needs to be accessible by an anonymous user
     */
    public function viewAction(Request $request, $externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $customerId = $this->getRequest()->get('customerId');

        if ($customerId == NULL) {
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
        if ($displayMenu)
            $displayMenu = $this->get('request')->get('timetableOnly', false) != true;

        return $this->renderLayout($request, $timetable, $externalStopPointId, false, $displayMenu);
    }
}
