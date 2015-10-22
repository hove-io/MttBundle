<?php

/**
 * Description of Network.
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class Navitia
{
    private $dateFormat = 'Ymd';

    protected $navitia_component;
    protected $navitia_sam;
    protected $translator;
    protected $securityContext;
    protected $applicationName;
    protected $customerManager;
    protected $requestStack;

    public function __construct(
        RequestStack $requestStack,
        $navitia_component,
        $navitia_sam,
        $translator,
        $em,
        $sc,
        $customerManager,
        $applicationName
    ) {
        $this->requestStack = $requestStack;
        $this->navitia_component = $navitia_component;
        $this->navitia_sam = $navitia_sam;
        $this->translator = $translator;
        $this->securityContext = $sc;
        $this->customerManager = $customerManager;
        $this->applicationName = $applicationName;

        $this->initToken();
    }

    private function initToken()
    {
        $customerId = $this->requestStack->getCurrentRequest()->get('customerId');
        if (empty($customerId)) {
            $customerId = $this->securityContext->getToken()->getUser()->getCustomer()->getId();
        }

        $navToken = $this->customerManager->getActiveNavitiaToken(
            $customerId,
            $this->applicationName
        );

        $this->navitia_sam->setToken($navToken);
    }

    /**
     * Get line routes.
     *
     * @param type $externalCoverageId
     * @param type $externalNetworkId
     * @param type $externalLineId
     *
     * @return type json
     */
    public function getLineRoutes(
        $externalCoverageId,
        $externalNetworkId,
        $externalLineId
    ) {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'path_filter' => 'networks/'.$externalNetworkId.'/lines/'.$externalLineId,
                'action' => 'routes',
            ),
        );
        $response = $this->navitia_component->call($query);

        return $response->routes;
    }

    public function getFirstLineAndRouteOfNetwork($externalCoverageId, $externalNetworkId)
    {
        $linesResponse = $this->navitia_sam->getLines($externalCoverageId, $externalNetworkId);
        $routes = $this->getLineRoutes($externalCoverageId, $externalNetworkId, $linesResponse->lines[0]->id);

        return array($linesResponse->lines[0]->id, $routes[0]->id);
    }

    /**
     * Get route StopPoints.
     *
     * @param type $externalCoverageId
     * @param type $externalNetworkId
     * @param type $externalLineId
     * @param type $externalRouteId
     *
     * @return type
     */
    public function getStopPoints(
        $externalCoverageId,
        $externalNetworkId,
        $externalLineId,
        $externalRouteId
    ) {
        return $this->navitia_sam->getStopPoints($externalCoverageId, $externalNetworkId, $externalLineId, $externalRouteId);
    }

    /**
     * Get one Stop Point.
     *
     * @param type $coverageId
     * @param type $networkId
     *
     * @return type
     */
    public function getStopPoint($coverageId, $stopPointId, $params)
    {
        $pathFilter = 'stop_points/'.$stopPointId;
        $parameters = http_build_query($params);

        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'path_filter' => $pathFilter,
                'parameters' => $parameters,
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Lines indexed by modes.
     *
     * @param string  $coverageId
     * @param type    $networkId
     * @param boolean $commercial if true commercial_modes returned, else physical_modes
     *
     * @return type
     */
    public function findAllLinesByMode($coverageId, $networkId)
    {
        $count = 30;
        $result = $this->navitia_sam->getLines($coverageId, $networkId, 1, $count);
        // no line found for this network
        if (empty($result) || !isset($result->lines)) {
            throw new \Exception(
                $this->translator->trans(
                    'services.navitia.no_lines_for_network',
                    array('%network%' => $networkId),
                    'exceptions'
                )
            );
        }

        if ($result->pagination->total_result > $count) {
            $result = $this->navitia_sam->getLines(
                $coverageId,
                $networkId,
                1,
                $result->pagination->total_result
            );
        }

        $lines_by_modes = array();
        foreach ($result->lines as $line) {
            if (!isset($lines_by_modes[$line->commercial_mode->id])) {
                $lines_by_modes[$line->commercial_mode->id] = array();
            }
            $lines_by_modes[$line->commercial_mode->id][] = $line;
        }

        return $lines_by_modes;
    }

    /**
     * Returns line title.
     *
     * @param string $coverageId
     * @param string $networkId
     * @param string $lineId
     *
     * @return type
     */
    public function getLineTitle($coverageId, $networkId, $lineId)
    {
        $response = $this->navitia_sam->getLine($coverageId, $networkId, $lineId);

        return ($response->lines[0]->name);
    }

    public function getRouteStopPoints($perimeter, $externalRouteId)
    {
        $pathFilter = 'networks/'.$perimeter->getExternalNetworkId().'/routes/'.$externalRouteId;

        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $perimeter->getExternalCoverageId(),
                'action' => 'route_schedules',
                'path_filter' => $pathFilter,
                'parameters' => '?depth=0',
            ),
        );

        return $this->navitia_component->call($query);
    }

    public function getStopPointsByRoute($coverageId, $networkId, $routeId)
    {
        $pathFilter = 'networks/'.$networkId.'/routes/'.$routeId;

        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'action' => 'stop_points',
                'path_filter' => $pathFilter,
                'parameters' => '?count=200',
            ),
        );

        return $this->navitia_component->call($query);
    }

    public function getLineFromRoute(
        $externalCoverageId,
        $externalNetworkId,
        $externalRouteId
    ) {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'path_filter' => 'networks/'.$externalNetworkId.'/routes/'.$externalRouteId,
                'action' => 'lines',
            ),
        );
        $response = $this->navitia_component->call($query);

        return $response->lines;
    }

    /**
     * Returns Stop Point pois.
     *
     * @param string $coverageId
     * @param string $stopPointId
     *
     * @return pois
     */
    public function getStopPointPois($externalCoverageId, $stopPointId, $distance = 400)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'places_nearby',
                'path_filter' => 'stop_points/'.$stopPointId,
                'parameters' => array(
                    'type' => array('poi'),
                    'filter' => 'poi_type.id=poi_type:Pointsdevente',
                    'distance' => $distance,
                    'count' => 2,
                ),
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Stop Point title.
     *
     * @param string $coverageId
     * @param string $networkId
     * @param string $lineId
     *
     * @return type
     */
    public function getRouteData($routeExternalId, $externalCoverageId)
    {
        // param depth '3' to get 'administrative_region' in response
        $response = $this->navitia_sam->getRoute($externalCoverageId, $routeExternalId, 3);
        if (!isset($response->routes) || empty($response->routes)) {
            throw new \Exception(
                $this->translator->trans(
                    'services.navitia.no_data_for_this_route',
                    array('%RouteId%' => $routeExternalId),
                    'exceptions'
                )
            );
        }

        return ($response->routes[0]);
    }

    /**
     * Returns Calendars for a route.
     *
     * @param string $externalCoverageId
     * @param string $externalRouteId
     *
     * @return object
     */
    public function getRouteCalendars($externalCoverageId, $externalRouteId, \DateTime $startDate, \DateTime $endDate)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'calendars',
                'path_filter' => 'routes/'.$externalRouteId,
                'parameters' => '?start_date='.$startDate->format($this->dateFormat).'&end_date='.$endDate->format($this->dateFormat),
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns a Calendar.
     *
     * @param string $externalCoverageId
     * @param string $calendarId
     *
     * @return object
     */
    public function getCalendar($externalCoverageId, $calendarId)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => $calendarId,
                'path_filter' => 'calendars'
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Calendars for a stop point and a route
     *
     * @param string $externalCoverageId
     * @param string $externalRouteId
     * @param string $externalStopPointId
     *
     * @return object
     */
    public function getStopPointCalendarsData($externalCoverageId, $externalRouteId, $externalStopPointId)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'calendars',
                'path_filter' => 'routes/'.$externalRouteId.'/stop_points/'.$externalStopPointId,
                'parameters' => '?count=100',
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Schedules for a calendar, a stop point and a route.
     *
     * @param string $externalCoverageId
     * @param string $externalRouteId
     * @param string $externalStopPointId
     * @param string $externalCalendarId
     *
     * @return object
     */
    public function getCalendarStopSchedulesByRoute($externalCoverageId, $externalRouteId, $externalStopPointId, $externalCalendarId)
    {
        // TODO: Retrieve fromdatetime from FUSIO
        // cf http://jira.canaltp.fr/browse/METH-196
        $fromdatetime = new \DateTime('now');
        $fromdatetime->setTime(4, 0);
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'stop_schedules',
                'path_filter' => 'routes/'.$externalRouteId.'/stop_points/'.$externalStopPointId,
                'parameters' => '?calendar='.$externalCalendarId.'&show_codes=true&from_datetime='.$fromdatetime->format('Ymd\THis'),
            ),
        );
        $stop_schedulesResponse = $this->navitia_component->call($query);
        // Since we give route id to navitia, only one route schedule is returned
        $response = new \stdClass();
        $response->stop_schedules = $stop_schedulesResponse->stop_schedules[0];
        $response->notes = isset($stop_schedulesResponse->notes) ? $stop_schedulesResponse->notes : array();
        $response->exceptions = isset($stop_schedulesResponse->exceptions) ? $stop_schedulesResponse->exceptions : array();

        return $response;
    }
}
