<?php


namespace CanalTP\MttBundle\Controller;
use CanalTP\MttBundle\CanalTPMttBundle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class LayoutLineController
 * @package CanalTP\MttBundle\Controller
 */
class TimecardController extends AbstractController
{
    /**
     * @param $externalNetworkId
     * @param bool $lineId
     * @param null $seasonId
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
            $listLineRoutes =  $navitia->getLineRoutes(
                $perimeter->getExternalCoverageId(),
                $externalNetworkId,
                $lineId
            );
            $externalRouteId = $listLineRoutes[0]->id;
            $lineInfo = $listLineRoutes[0]->line;
        }


        return $this->render(
            'CanalTPMttBundle:Timecard:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'externalLineId'    => $lineId,
                'currentSeasonId'   => $currentSeasonId,
                'currentSeason'     => $currentSeason,
                'externalRouteId'   => $externalRouteId,
                'options'           => array(
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $externalNetworkId, $externalLineId, $externalRouteId)
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

        $timecard = $this->get('canal_tp_mtt.Timecard_manager')->findByUniqueString(
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
            foreach($haystack as $key => $value) {
                $current_key=$key;
                if( $needle === $value ||
                    ( is_object($value) && $array_search_recursif($needle, $value) !== false) ) {
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request, $externalNetworkId, $externalLineId, $externalRouteId)
    {

        if ($this->saveList($request, $externalLineId, $externalRouteId, $externalNetworkId)) {
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
                    'seasonId' => $request->get('seasonId')
                )
            )
        );
    }

    /**
     * @return bool
     */
    private function saveList(Request $request, $externalLineId, $externalRouteId, $externalNetworkId)
    {
        $stopPoints = $request->get('stopPoints');
        $seasonId = $request->get('seasonId');
        $route = $request->get('route');

        $externalRouteId = (is_null($route)) ? $externalRouteId : $route;

        $getAllStopPoints = !empty($stopPoints);

        if ($getAllStopPoints) {
            $timecard = $this->get('canal_tp_mtt.timecard_manager')->findByUniqueString(
                $externalLineId,
                $externalRouteId,
                $seasonId,
                $externalNetworkId
            );

            $timecard->setStopPoints($stopPoints);

            $em = $this->getDoctrine()->getManager();
            $em->persist($timecard);
            $em->flush($timecard);
        }

        return ($getAllStopPoints);

    }
}