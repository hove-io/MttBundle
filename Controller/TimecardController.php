<?php


namespace CanalTP\MttBundle\Controller;
use CanalTP\MttBundle\CanalTPMttBundle;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class LayoutLineController
 * @package CanalTP\MttBundle\Controller
 */
class TimecardController extends AbstractController
{

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

        // Get the direction if is defined
        $index = (is_null($request->query->get('direction'))) ? 0 : 1;

        return $this->render(
            'CanalTPMttBundle:Timecard:edit.html.twig',
            array(
                'pageTitle' => 'Editer la fiche ligne',
                'externalNetworkId' => $externalNetworkId,
                'externalLineId' => $externalLineId,
                'routes' => $routes,
                'stopPoints' => $stopPoints->stop_points,
                'stopPointsIncluded' => $stopPointsList,
                'lineId' => $routes[$index]->line->code,
                'currentDirectionName' => $routes[$index]->name,
                'currentDirectionId' => $routes[$index]->direction->id,
                'routeId' => $routes[$index]->id,
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