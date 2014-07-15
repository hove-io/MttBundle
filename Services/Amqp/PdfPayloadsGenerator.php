<?php

/**
 * Generates payloads for Amqp messages in order to generate pdf for a whole season.
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;

use CanalTP\MttBundle\Services\Navitia;
use CanalTP\MttBundle\Services\TimetableManager;
use CanalTP\MttBundle\Services\StopPointManager;

class PdfPayloadsGenerator
{
    private $co = null;
    private $router = null;
    private $navitia = null;
    private $timetableManager = null;
    private $stopPointManager = null;

    public function __construct(
        Container $co,
        Router $router,
        Navitia $navitia,
        TimetableManager $timetableManager,
        StopPointManager $stopPointManager
    )
    {
        $this->co = $co;
        $this->router = $router;
        $this->navitia = $navitia;
        $this->timetableManager = $timetableManager;
        $this->stopPointManager = $stopPointManager;
    }

    // construct payload for AMQP message
    private function getPayload($network, $season, $lineConfig, $externalRouteId, $stopPoint)
    {
        $payload = array();
        $payload['pdfHash'] = isset($stopPoint->pdfHash) ? $stopPoint->pdfHash : '';
        $payload['layoutParams'] = array(
            'orientation' => $lineConfig->getLayout()->getOrientation(),
        );
        $payload['cssVersion'] = $lineConfig->getLayout()->getCssVersion();
        $payload['url'] = $this->co->get('request')->getScheme() . '://';
        $payload['url'] .= $this->co->get('request')->getHttpHost();
        $payload['url'] .= $this->router->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId'     => $network->getExternalId(),
                'externalLineId'        => $lineConfig->getExternalLineId(),
                'externalRouteId'       => $externalRouteId,
                'seasonId'              => $season->getId(),
                'externalStopPointId'   => $stopPoint->id,
                'timetableOnly'         => true
            )
        );
        $payload['timetableParams'] = array(
            'seasonId'              => $season->getId(),
            'externalNetworkId'     => $network->getExternalId(),
            'externalRouteId'       => $externalRouteId,
            'externalLineId'        => $lineConfig->getExternalLineId(),
            'externalStopPointId'   => $stopPoint->id,
        );

        return $payload;
    }

    private function getRouteEnhancedStopPoints($network, $externalRouteId, $timetable)
    {
        $routeSchedulesData = $this->navitia->getRouteStopPoints($network, $externalRouteId);
        if (isset($routeSchedulesData->route_schedules[0])) {
            if (!empty($timetable)) {
                $stopPoints = $this->stopPointManager->enhanceStopPoints(
                    $routeSchedulesData->route_schedules[0]->table->rows,
                    $timetable
                );
            } else {
                $stopPoints = $routeSchedulesData->route_schedules[0]->table->rows;
            }
        }

        return $stopPoints;
    }

    public function getStopPointsPayloads($timetable, $stopPointsExternalIds)
    {
        $lineConfig = $timetable->getLineConfig();
        $externalRouteId = $timetable->getExternalRouteId();
        $season = $lineConfig->getSeason();
        $network = $season->getNetwork();
        $routeEnhancedStopPoints = $this->getRouteEnhancedStopPoints($network, $externalRouteId, $timetable);
        $payloads = array();
        foreach ($routeEnhancedStopPoints as $enhancedStopPoint) {
            if (in_array($enhancedStopPoint->stop_point->id, $stopPointsExternalIds)) {
                $payloads[] = $this->getPayload(
                    $network,
                    $season,
                    $lineConfig,
                    $externalRouteId,
                    $enhancedStopPoint->stop_point
                );
            }
        }

        return $payloads;
    }

    public function getSeasonPayloads($season)
    {
        $payloads = array();
        $network = $season->getNetwork();
        foreach ($season->getLineConfigs() as $lineConfig) {
            $externalLineId = $lineConfig->getExternalLineId();
            $routes = $this->navitia->getLineRoutes(
                $network->getExternalCoverageId(),
                $network->getExternalId(),
                $externalLineId
            );
            foreach ($routes as $route) {
                $timetable = $this->timetableManager->findTimetableByExternalRouteIdAndLineConfig(
                    $route->id,
                    $lineConfig
                );
                $stopPoints = $this->getRouteEnhancedStopPoints($network, $route->id, $timetable);
                foreach ($stopPoints as $stopPoint) {
                    $payloads[] = $this->getPayload(
                        $network,
                        $season,
                        $lineConfig,
                        $route->id,
                        $stopPoint->stop_point
                    );
                }
            }
        }
        if (empty($payloads)) {
            throw new \Exception('pdfGeneration.no_pdf');
        }

        return $payloads;
    }
}
