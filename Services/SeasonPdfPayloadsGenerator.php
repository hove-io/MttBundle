<?php

/**
 * Generates payloads for Amqp messages in order to generate pdf for a whole season.
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;

class SeasonPdfPayloadsGenerator
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
        $payload['mediaManagerParams'] = array(
            'externalNetworkId'     => $network->getExternalId(),
            'externalRouteId'       => $externalRouteId,
            'externalStopPointId'   => $stopPoint->id,
            'seasonId'              => $season->getId(),
        );
        return $payload;
    }

    public function generate($season)
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
                $routeSchedulesData = $this->navitia->getRouteStopPoints($network, $route->id);
                if (isset($routeSchedulesData->route_schedules[0])) {
                    $timetable = $this->timetableManager->findTimetableByExternalRouteIdAndLineConfig(
                        $route->id, 
                        $lineConfig
                    );
                    if (!empty($timetable)) {
                        $stopPoints = $this->stopPointManager->enhanceStopPoints(
                            $routeSchedulesData->route_schedules[0]->table->rows,
                            $timetable
                        );
                    }
                }
                foreach($stopPoints as $stopPoint) {
                    
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
            throw new \Exception('No pdf to generate for this season');
        }
        return $payloads;
    }
}
