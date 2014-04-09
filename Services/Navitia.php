<?php

/**
 * Description of Network
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

class Navitia
{
    private $dateFormat = 'Ymd';

    protected $navitia_component;
    protected $navitia_iussaad;
    protected $translator;

    public function __construct($navitia_component, $navitia_iussaad, $translator)
    {
        $this->navitia_component = $navitia_component;
        $this->navitia_iussaad = $navitia_iussaad;
        $this->translator = $translator;
    }

    /**
     * Get one Stop Point
     *
     * @param  type $coverageId
     * @param  type $networkId
     * @return type 
     */
    public function getStopPoint($coverageId, $stopPointId, $params)
    {
        $filter = 'stop_points/' . $stopPointId;
        $parameters = http_build_query($params);
        
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'filter' => $filter,
                'parameters' => $parameters
            )
        );
        return $this->navitia_component->call($query);
    }

    /**
     * Returns Lines indexed by modes
     *
     * @param  String  $coverageId
     * @param  type    $networkId
     * @param  Boolean $commercial if true commercial_modes returned, else physical_modes
     * @return type
     */
    public function findAllLinesByMode($coverageId, $networkId)
    {
        $count = 30;
        $result = $this->navitia_iussaad->getLines($coverageId, $networkId, 1, $count);
        // no line found for this network
        if (empty($result) || !isset($result->lines)) {
            throw new \Exception(
                $this->translator->trans(
                    'services.navitia.no_lines_for_network', 
                    array('%network%'=>$networkId), 
                    'exceptions'
                )
            );
        }

        if ($result->pagination->total_result > $count) {
            $result = $this->navitia_iussaad->getLines(
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
     * Returns line title
     *
     * @param  String $coverageId
     * @param  String $networkId
     * @param  String $lineId
     * @return type
     */
    public function getLineTitle($coverageId, $networkId, $lineId)
    {
        $response = $this->navitia_iussaad->getLine($coverageId, $networkId, $lineId);

        return ($response->lines[0]->name);
    }

    // TODO: Move this function in SamBundle
    /**
     * Returns coverages
     *
     * @return coverages
     */
    public function getCoverages()
    {
        $query = array('api' => 'coverage');

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Stop Point title
     *
     * @param  String $coverageId
     * @param  String $stopPointId
     * @return type
     */
    public function getStopPointTitle($coverageId, $stopPointId)
    {
        $response = $this->navitia_iussaad->getStopPoint($coverageId, $stopPointId);

        return ($response->stop_points[0]->name);
    }

    /**
     * Returns Stop Point external code
     *
     * @param  String $coverageId
     * @param  String $stopPointId
     * @return external_code
     */
    public function getStopPointExternalCode($coverageId, $stopPointId)
    {
        $response = $this->getStopPoint($coverageId, $stopPointId, array('depth' => 1, 'show_codes' => 'true'));
        $externalCode = null;

        foreach ($response->stop_points[0]->codes as $code) {
            if ($code->type == 'external_code') {
                $externalCode = substr($code->value, 3);
                break ;
            }
        }
        return ($externalCode);
    }

    /**
     * Returns Stop Point title
     *
     * @param  String $coverageId
     * @param  String $networkId
     * @param  String $lineId
     * @return type
     */
    public function getRouteData($routeExternalId, $externalCoverageId)
    {
        $response = $this->navitia_iussaad->getRoute($externalCoverageId, $routeExternalId);
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
     * Returns Calendars for a route
     *
     * @param String $externalCoverageId
     * @param String $externalRouteId
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
                'filter' => 'routes/' . $externalRouteId,
                'parameters' => '?start_date=' . $startDate->format($this->dateFormat) . '&end_date=' . $endDate->format($this->dateFormat)
            )
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Calendars for a stop point and a route
     *
     * @param String $externalCoverageId
     * @param String $externalRouteId
     * @param String $externalStopPointId
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
                'filter' => 'routes/' . $externalRouteId . '/stop_points/' . $externalStopPointId,
            )
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Schedules for a calendar, a stop point and a route
     *
     * @param String $externalCoverageId
     * @param String $externalRouteId
     * @param String $externalStopPointId
     * @param String $externalCalendarId
     *
     * @return object
     */
    public function getCalendarStopSchedulesByRoute($externalCoverageId, $externalRouteId, $externalStopPointId, $externalCalendarId)
    {
         $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'stop_schedules',
                'filter' => 'routes/' . $externalRouteId . '/stop_points/' . $externalStopPointId,
                'parameters' => '?calendar=' . $externalCalendarId
            )
        );
        $stop_schedulesResponse = $this->navitia_component->call($query);
        // Since we give route id to navitia, only one route schedule is returned
        $response = new \stdClass;
        $response->stop_schedules = $stop_schedulesResponse->stop_schedules[0];
        $response->notes = isset($stop_schedulesResponse->notes) ? $stop_schedulesResponse->notes : array();
        $response->exceptions = isset($stop_schedulesResponse->exceptions) ? $stop_schedulesResponse->exceptions : array();

        return $response;
    }
}
