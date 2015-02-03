<?php


namespace CanalTP\MttBundle\Controller;
use CanalTP\MttBundle\CanalTPMttBundle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class TimecardController
 * @package CanalTP\MttBundle\Controller
 */
class TimecardController extends AbstractController
{
    /**
     * @param $externalNetworkId
     * @param mixed $lineId
     * @param mixed $seasonId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($externalNetworkId, $lineId = false, $seasonId = null)
    {
        /** @var $navitia \CanalTP\MttBundle\Services\Navitia */
        $navitia = $this->get('canal_tp_mtt.navitia');
        $customer = $this->getUser()->getCustomer();

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );

        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter($perimeter);
        $currentSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);
        $currentSeasonId = empty($currentSeason) ? false : $currentSeason->getId();

        if (empty($lineId)) {
            list($lineId, $externalRouteId) = $navitia->getFirstLineAndRouteOfNetwork(
                $perimeter->getExternalCoverageId(),
                $externalNetworkId
            );
        } else {
            $listLineRoutes = $navitia->getLineRoutes(
                $perimeter->getExternalCoverageId(),
                $externalNetworkId,
                $lineId
            );
            $externalRouteId = $listLineRoutes[0]->id;
        }

        $lineInfo = $navitia->getLine($perimeter->getExternalCoverageId(), $externalNetworkId, $lineId);

        $lineConfig = $this->getDoctrine()->getRepository(
            'CanalTPMttBundle:LineConfig'
        )->findOneBy(array('externalLineId' => $lineId, 'season' => $currentSeasonId));


        if (!empty($lineConfig)) {
            /** @var \CanalTP\MttBundle\Services\LineTimecardManager $lineTimecardManager */
            $lineTimecardManager = $this->get('canal_tp_mtt.line_timecard_manager');

            $lineTimecard = $lineTimecardManager->createLineTimecardIfNotExist(
                $lineId,
                $externalNetworkId,
                $lineConfig
            );
        }

        $lineTimecardId =  (isset($lineTimecard)) ? $lineTimecard->getId() : null ;


        return $this->render(
            'CanalTPMttBundle:Timecard:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'externalLineId' => $lineId,
                'currentLine' => $lineInfo,
                'lineConfig' => $lineConfig,
                'currentSeasonId' => $currentSeasonId,
                'currentSeason' => $currentSeason,
                'externalRouteId' => $externalRouteId,
                'lineTimecardId' => $lineTimecardId,
                'options' => array(
                    'no_route' => true,
                    'current_line' => $lineId
                )
            )
        );
    }

    /**
     * @param Request $request
     * @param $externalNetworkId
     * @param $externalLineId
     * @param $externalRouteId
     * @param $lineTimecardId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $externalNetworkId, $externalLineId, $externalRouteId, $lineTimecardId)
    {
        /** @var $navitia \CanalTP\MttBundle\Services\Navitia */
        $navitia = $this->get('canal_tp_mtt.navitia');
        $customer = $this->getUser()->getCustomer();

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );

        $seasonId = $request->get('seasonId');
        $externalCoverageId = $perimeter->getExternalCoverageId();

        $routes = $navitia->getLineRoutes(
            $externalCoverageId,
            $externalNetworkId,
            $externalLineId
        );

        $stopPoints = $navitia->getStopPointsByRoute($externalCoverageId,
            $externalNetworkId,
            $externalRouteId
        );

        $timecard = $this->get('canal_tp_mtt.Timecard_manager')->findByCompositeKey(
            $externalLineId,
            $externalRouteId,
            $seasonId,
            $externalNetworkId
        );

        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        $stopPointsList = null;
        $stopPointsIncluded = $timecard->getStopPoints();
        if (!empty($stopPointsIncluded)) {
            $stopPointsList = $stopPointManager->enrichStopPoints(
                $timecard->getStopPoints(),
                $perimeter->getExternalCoverageId(),
                $perimeter->getExternalNetworkId()
            );
        }

        $array_search_recursif = function ($needle, $haystack) use (&$array_search_recursif) {
            foreach ($haystack as $key => $value) {
                $current_key = $key;
                if ($needle === $value ||
                    (is_object($value) && $array_search_recursif($needle, $value) !== false)
                ) {
                    return $current_key;
                }
            }
            return false;
        };

        // Get table index of specified route id
        $routeIndex = $array_search_recursif($externalRouteId, $routes);

        if (false === $routeIndex) {
            throw new Exception('Route Id not found');
        }

        return $this->render(
            'CanalTPMttBundle:Timecard:edit.html.twig',
            array(
                'pageTitle' => 'Editer la fiche ligne',
                'externalNetworkId' => $externalNetworkId,
                'externalLineId' => $externalLineId,
                'lineTimecardId' => $lineTimecardId,
                'routes' => $routes,
                'stopPoints' => $stopPoints->stop_points,
                'stopPointsIncluded' => $stopPointsList,
                'lineId' => $routes[$routeIndex]->line->code,
                'currentDirectionName' => $routes[$routeIndex]->name,
                'currentRouteId' => $externalRouteId,
                'seasonId' => $seasonId,
                'timecard' => $timecard
            )
        );
    }

    /**
     * @param Request $request
     * @param $externalNetworkId
     * @param $externalLineId
     * @param $externalRouteId
     * @param $lineTimecardId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request, $externalNetworkId, $externalLineId, $externalRouteId, $lineTimecardId)
    {

        if ($this->saveList($request, $externalLineId, $externalRouteId, $externalNetworkId, $lineTimecardId)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'timecard.confirm_order_saved',
                    array(),
                    'default'
                )
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_timecard_edit',
                array(
                    'externalNetworkId' => $externalNetworkId,
                    'externalLineId' => $externalLineId,
                    'externalRouteId' => $externalRouteId,
                    'seasonId' => $request->get('seasonId'),
                    'lineTimecardId' => $lineTimecardId
                )
            )
        );
    }

    /**
     * @param Request $request
     * @param $externalLineId
     * @param $externalRouteId
     * @param $externalNetworkId
     * @param $lineTimecardId
     * @return bool
     */
    private function saveList(Request $request, $externalLineId, $externalRouteId, $externalNetworkId, $lineTimecardId)
    {
        $stopPoints = $request->get('stopPoints');
        $seasonId = $request->get('seasonId');
        $route = $request->get('route');

        $externalRouteId = (is_null($route)) ? $externalRouteId : $route;

        $getAllStopPoints = !empty($stopPoints);

        if ($getAllStopPoints) {

            $timecard = $this->get('canal_tp_mtt.timecard_manager')->findByCompositeKey(
                $externalLineId,
                $externalRouteId,
                $seasonId,
                $externalNetworkId
            );

            $timecard->setStopPoints($stopPoints);

            $lineTimecard = $this->getDoctrine()->getRepository('CanalTPMttBundle:LineTimecard')->find($lineTimecardId);
            $timecard->setLineTimecard($lineTimecard);

            $em = $this->getDoctrine()->getManager();
            $em->persist($timecard);
            $em->flush($timecard);
        }

        return ($getAllStopPoints);

    }

    /**
     * @param $externalNetworkId
     * @param $externalLineId
     * @param $seasonId
     */
    public function editLayoutAction($externalNetworkId, $externalLineId, $seasonId)
    {
        $this->isGranted('BUSINESS_EDIT_LAYOUT');

        /** @var \CanalTP\MttBundle\Services\LineTimecardManager $lineTimecardManager */
        $lineTimecardManager = $this->get('canal_tp_mtt.line_timecard_manager');

        $lineTimecard = $lineTimecardManager->getLineTimecard(
            $externalLineId,
            $externalNetworkId
        );


        return $this->renderLayout($lineTimecard);
    }

    /**
     * @param LineTimecard $lineTimecard
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderLayout($lineTimecard)
    {
        $externalCoverageId = $lineTimecard->getLineConfig()->getSeason()->getPerimeter()->getExternalCoverageId();
        $layoutConfig = json_decode($lineTimecard->getLineConfig()->getLayoutConfig()->getLayout()->getConfiguration());
        //$lineId = $lineTimecard->getLineCongig()->getExternalLineId();

        // Get line calendars
        $calendarsAndNotes = $this->get('canal_tp_mtt.calendar_manager')->getTimecardCalendars($externalCoverageId, $lineTimecard);

        return $this->render(
            'CanalTPMttBundle:Layouts:' . $layoutConfig->lineTpl->templateName,
            array(
                'editable'              => true,
                'blockTypes'            => $this->container->getParameter('blocks'),
                'lineTimecard'          => $lineTimecard,
                'displayMenu'           => false,
                'stopPoint'             => false,
                'layout'                => $lineTimecard->getLineConfig()->getLayoutConfig(),
                'notesType'             => $lineTimecard->getLineConfig()->getLayoutConfig()->getNotesType(),
                'externalNetworkId'     => $lineTimecard->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'calendars'             => $calendarsAndNotes['calendars'],
                'notes'                 => $calendarsAndNotes['notes'],
                'templatePath'          => '@CanalTPMtt/Layouts/uploads/' . $lineTimecard->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/',
                'imgPath'               => 'bundles/canaltpmtt/img/uploads/' . $lineTimecard->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/',
                'cssPath'               => 'bundles/canaltpmtt/css/uploads/' . $lineTimecard->getLineConfig()->getLayoutConfig()->getLayout()->getId() . '/'
            )
        );
    }

}