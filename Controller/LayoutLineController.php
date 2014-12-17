<?php


namespace CanalTP\MttBundle\Controller;
use CanalTP\MttBundle\CanalTPMttBundle;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class LayoutLineController
 * @package CanalTP\MttBundle\Controller
 */
class LayoutLineController extends AbstractController
{
    /**
     * @param $externalNetworkId
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

        // Get the direction if is defined
        $index = (is_null($request->query->get('direction'))) ? 0 : 1;

        return $this->render(
            'CanalTPMttBundle:LayoutLine:edit.html.twig',
            array(
                'pageTitle' => 'Editer la fiche ligne',
                'externalNetworkId' => $externalNetworkId,
                'routes' => $routes,
                'stopPoints' => $stopPoints->stop_points,
                'lineId' => $routes[$index]->line->code,
                'currentDirectionName' => $routes[$index]->name,
                'currentDirectionId' => $routes[$index]->direction->id,
                'routeId' => $routes[$index]->id
            )
        );
    }
}