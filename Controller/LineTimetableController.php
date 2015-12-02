<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CanalTP\MttBundle\Entity\Template;

class LineTimetableController extends AbstractController
{
    /**
     * Listing all available lines and displaying a LineTimetable configuration for selected one
     * If no externalLineId is provided, selecting first line found in Navitia by default
     *
     * @param $externalNetworkId
     * @param mixed $externalLineId
     * @param mixed $seasonId
     */
    public function listAction($externalNetworkId, $externalLineId = null, $seasonId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $navitia = $this->get('canal_tp_mtt.navitia');
        $customer = $this->getUser()->getCustomer();

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );

        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter($perimeter);
        $currentSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);

        // No externalLineId provided, get first one found
        if (empty($externalLineId)) {
            $externalLineId = $navitia->getFirstLineOfNetwork(
                $perimeter->getExternalCoverageId(),
                $externalNetworkId
            );
        }

        $externalLineData = $navitia->getLine(
            $perimeter->getExternalCoverageId(),
            $externalNetworkId,
            $externalLineId
        );

        $lineConfig = $this->getDoctrine()
            ->getRepository('CanalTPMttBundle:LineConfig')
            ->findOneBy(
                array(
                    'externalLineId'    => $externalLineId,
                    'season'            => $currentSeason
                )
            );

        if (!empty($lineConfig)) {
            $lineTimetable = $this->get('canal_tp_mtt.line_timetable_manager')
                ->findOrCreateLineTimetable($lineConfig);
        }

        return $this->render(
            'CanalTPMttBundle:LineTimetable:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'currentSeason'     => $currentSeason,
                'seasons'           => $seasons,
                'externalLineData'  => $externalLineData,
                'externalLineId'    => $externalLineData->id,
                'lineTimetable'     => isset($lineTimetable) ? $lineTimetable : null
            )
        );
    }

    /**
     * Selecting stops displayed in the LineTimetable.
     *
     * @param integer $lineTimetableId
     * @param integer $seasonId
     * @param string $externalNetworkId
     * @param string $externalRouteId
     */
    public function selectStopsAction(Request $request, $lineTimetableId, $seasonId, $externalNetworkId, $externalRouteId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $lineTimetable = $this->getDoctrine()
            ->getRepository('CanalTPMttBundle:LineTimetable')
            ->find($lineTimetableId);

        // On POST request, save selected stop points
        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST') {
            try {
                $this->get('canal_tp_mtt.selected_stop_point_manager')->updateStopPointSelection($request->getContent(), $lineTimetable);
                $this->addFlash('success', 'line_timetable.flash.saved_stop_points');
                return $this->redirectToRoute(
                    'canal_tp_mtt_line_timetable_select_stops',
                    array(
                        'lineTimetableId' => $lineTimetableId,
                        'seasonId' => $seasonId,
                        'externalNetworkId' => $externalNetworkId,
                        'externalRouteId' => $externalRouteId
                    )
                );
            } catch (\Exception $e) {
                return new Response($e->getMessage());
            }
        }

        $navitia = $this->get('canal_tp_mtt.navitia');
        $customer = $this->getUser()->getCustomer();

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );

        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $seasons = $seasonManager->findByPerimeter($perimeter);
        $currentSeason = $seasonManager->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);

        $externalLineId = $lineTimetable->getLineConfig()->getExternalLineId();
        $externalCoverageId = $perimeter->getExternalCoverageId();

        $routes = $navitia->getLineRoutes(
            $externalCoverageId,
            $externalNetworkId,
            $externalLineId
        );

        if ($externalRouteId == null) {
            $externalRouteId = $routes[0]->id;
        }

        $externalLineData = array(
            'code' => $routes[0]->line->code,
            'color' => $routes[0]->line->color
        );

        list($availableStopPoints, $reversed) = $this->get('canal_tp_mtt.selected_stop_point_manager')
            ->prepareStopsSelection(
                $externalCoverageId,
                $externalRouteId,
                $lineTimetable,
                $routes
            );

        if ($reversed) {
            $this->addFlash('info', 'line_timetable.flash.reversed');
        }

        return $this->render(
            'CanalTPMttBundle:LineTimetable:selectStopPoints.html.twig',
            array(
                'externalNetworkId'     => $externalNetworkId,
                'currentSeason'         => $currentSeason,
                'externalLineId'        => $externalLineId,
                'externalRouteId'       => $externalRouteId,
                'lineTimetable'         => $lineTimetable,
                'externalLineData'      => $externalLineData,
                'routes'                => $routes,
                'availableStopPoints'   => $availableStopPoints,
                'selectedStopPoints'    => $lineTimetable->getSelectedStopPointsByRoute($externalRouteId)
            )
        );
    }

    /**
     * Displaying line schedule
     *
     * @param string $externalNetworkId
     * @param string $externalLineId
     */
    public function showScheduleAction($externalNetworkId, $externalLineId)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $externalCoverageId = $perimeter->getExternalCoverageId();

        $schedule = $this->get('canal_tp_mtt.calendar_manager')->getCalendarsForLine(
            $externalCoverageId,
            $externalNetworkId,
            $externalLineId
        );

        $navitia = $this->get('canal_tp_mtt.navitia');
        $line = $navitia->getLine(
            $externalCoverageId,
            $externalNetworkId,
            $externalLineId
        );

        $externalLineData = array(
            'code' => $line->code,
            'color' => $line->color
        );

        return $this->render(
            'CanalTPMttBundle:LineTimetable:schedule.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'externalLineId'    => $externalLineId,
                'externalLineData'  => $externalLineData,
                'navigationMode'    => 'lines',
                'schedule'          => $schedule
            )
        );
    }
}
