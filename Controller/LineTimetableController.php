<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use CanalTP\MttBundle\Entity\Template;
use CanalTP\MttBundle\Entity\BlockRepository;

class LineTimetableController extends AbstractController
{
    const EDIT_MODE = 'edit';
    const VIEW_MODE = 'view';
    const PRINT_MODE = 'print';

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

        $externalLineData = $navitia->getLine(
            $perimeter->getExternalCoverageId(),
            $externalNetworkId,
            $externalLineId
        );

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
     * Rendering a layout
     *
     * @param string $externalNetworkId
     * @param string $externalLineId
     * @param string $mode
     */
    public function renderAction($externalNetworkId, $externalLineId, $seasonId, $mode)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter($perimeter);
        $currentSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);

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

        if (empty($lineTimetable)) {
            $this->addFlashMessage('danger', 'error.line_timetable.not_found');
            $this->redirect($request->headers->get('referer'));
        }

        // Checking the associated Layout has a Template of type LINE_TYPE before rendering it
        if (!$lineTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getTemplate(Template::LINE_TYPE)) {
            $this->addFlashMessage('danger', 'error.template.not_found', array('%type%' => Template::LINE_TYPE));
            return $this->redirect($request->headers->get('referer'));
        }

        $navitia = $this->get('canal_tp_mtt.navitia');

        $externalCoverageId = $perimeter->getExternalCoverageId();

        $line = $navitia->getLine(
            $externalCoverageId,
            $externalNetworkId,
            $externalLineId
        );

        $routes = $navitia->getLineRoutes(
            $externalCoverageId,
            $externalNetworkId,
            $externalLineId
        );

        //setlocale(LC_TIME, "fr_FR.UTF8"); => if needed later, set a parameter in container
        $layoutId = $lineTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getId();
        $templateFile = $lineTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getTemplate(Template::LINE_TYPE)->getPath();

        return $this->render(
            'CanalTPMttBundle:Layouts:' . $templateFile,
            array(
                'pageTitle'             => 'line_timetable.title.' . $mode,
                'timetable'             => $lineTimetable,
                'externalNetworkId'     => $externalNetworkId,
                'externalLineId'        => $externalLineId,
                'currentSeason'         => $currentSeason,
                'orientation'           => $lineTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getOrientationAsString(),
                'line'                  => $line,
                'routes'                => $routes,
                'mode'                  => $mode,
                'templatePath'          => '@CanalTPMtt/Layouts/uploads/' . $layoutId . '/',
                'imgPath'               => 'bundles/canaltpmtt/img/uploads/' . $layoutId . '/',
                'cssPath'               => 'bundles/canaltpmtt/css/uploads/' . $layoutId . '/'
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

    /**
     * Loading a calendar (Ajax request)
     *
     * @param string $externalNetworkId
     * @param integer $blockId
     * @param integer $columnsLimit
     *
     */
    public function loadCalendarAction(Request $request, $externalNetworkId, $blockId, $columnsLimit)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $this->isPostAjax($request);

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $block = $this->get('canal_tp_mtt.block_manager')->findBlock($blockId);
        $timetable = $block->getLineTimetable();

        // checking that the provided block is a calendar with data and is accessible via the network
        if (empty($timetable)) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_not_linked',
                    array('%blockId%' => $blockId)
                ),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($timetable->getLineConfig()->getSeason()->getPerimeter() != $perimeter) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.bad_external_network',
                    array('%externalNetworkId%' => $externalNetworkId)
                ),
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }
        if ($block->getContent() == null) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_empty',
                    array('%blockId%' => $blockId)
                ),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($block->getType() !== BlockRepository::CALENDAR_TYPE) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_not_calendar',
                    array('%blockId%' => $blockId)
                ),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $parameters = array();

        $selectedStopPoints = $block->getLineTimetable()->getSelectedStopPointsByRoute($block->getExternalRouteId());

        if (!$selectedStopPoints->isEmpty()) {
            $parameters['stopPoints'] = $selectedStopPoints;
        }

        $schedule = $this->get('canal_tp_mtt.calendar_manager')->getCalendarForBlock(
            $perimeter->getExternalCoverageId(),
            $block,
            $parameters
        );

        $layoutId = $block->getLineTimetable()->getLineConfig()->getLayoutConfig()->getLayout()->getId();

        return $this->render(
            'CanalTPMttBundle:LineTimetable:blockCalendar.html.twig',
            array(
                'templatePath'  => '@CanalTPMtt/Layouts/uploads/' . $layoutId . '/',
                'schedule'      => $schedule,
                'columnsLimit'  => $columnsLimit
            )
        );
    }
}
