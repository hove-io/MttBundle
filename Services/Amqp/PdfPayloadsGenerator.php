<?php

/**
 * Generates payloads for Amqp messages in order to generate pdf for a whole season.
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\TranslatorInterface;

use CanalTP\MttBundle\Services\Navitia;
use CanalTP\MttBundle\Services\StopTimetableManager;
use CanalTP\MttBundle\Services\StopPointManager;
use CanalTP\MttBundle\Services\LineManager;

class PdfPayloadsGenerator
{
    private $co = null;
    private $router = null;
    private $navitia = null;
    private $stopTimetableManager = null;
    private $stopPointManager = null;
    private $lineManager = null;
    private $logger = null;

    public function __construct(
        Container $co,
        Router $router,
        Navitia $navitia,
        StopTimetableManager $stopTimetableManager,
        StopPointManager $stopPointManager,
        LineManager $lineManager,
        Logger $logger,
        TranslatorInterface $translator
    ) {
        $this->co = $co;
        $this->logger = $logger;
        $this->router = $router;
        $this->navitia = $navitia;
        $this->stopTimetableManager = $stopTimetableManager;
        $this->stopPointManager = $stopPointManager;
        $this->lineManager = $lineManager;
        $this->translator = $translator;
    }

    private function generatePayloadUrl()
    {
        if ($this->co->hasParameter('canal_tp_mtt.payload_host') && !is_null($this->co->getParameter('canal_tp_mtt.payload_host'))) {
            $url = 'http://' . $this->co->getParameter('canal_tp_mtt.payload_host');
        } else {
            $url = $this->co->get('request')->getScheme() . '://';
            $url .= $this->co->get('request')->getHttpHost();
        }

        return ($url);
    }

    // construct payload for AMQP message
    private function getPayload($perimeter, $season, $lineConfig, $externalRouteId, $stopPoint)
    {
        $payload = array();
        $payload['pdfHash'] = isset($stopPoint->pdfHash) ? $stopPoint->pdfHash : '';
        $payload['layoutParams'] = array(
            'orientation' => $lineConfig->getLayoutConfig()->getLayout()->getOrientationAsString(),
        );
        $payload['cssVersion'] = $lineConfig->getLayoutConfig()->getLayout()->getCssVersion();
        $payload['url'] = $this->generatePayloadUrl();
        $payload['url'] .= $this->router->generate(
            'canal_tp_mtt_stop_timetable_view',
            array(
                'externalNetworkId'     => $perimeter->getExternalNetworkId(),
                'externalLineId'        => $lineConfig->getExternalLineId(),
                'externalRouteId'       => $externalRouteId,
                'seasonId'              => $season->getId(),
                'externalStopPointId'   => $stopPoint->id,
                'customerId'            => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
                'stopTimetableOnly'         => true
            )
        );
        $payload['stopTimetableParams'] = array(
            'seasonId'              => $season->getId(),
            'externalNetworkId'     => $perimeter->getExternalNetworkId(),
            'externalRouteId'       => $externalRouteId,
            'externalLineId'        => $lineConfig->getExternalLineId(),
            'externalStopPointId'   => $stopPoint->id,
        );

        return $payload;
    }

    private function getRouteEnhancedStopPoints($perimeter, $externalRouteId, $stopTimetable)
    {
        $routeSchedulesData = $this->navitia->getRouteStopPoints($perimeter, $externalRouteId);
        if (isset($routeSchedulesData->route_schedules[0])) {
            if (!empty($stopTimetable)) {
                $stopPoints = $this->stopPointManager->enhanceStopPoints(
                    $routeSchedulesData->route_schedules[0]->table->rows,
                    $stopTimetable
                );
            } else {
                $stopPoints = $routeSchedulesData->route_schedules[0]->table->rows;
            }
        }

        return $stopPoints;
    }

    public function getStopPointsPayloads($stopTimetable, $stopPointsExternalIds)
    {
        $lineConfig = $stopTimetable->getLineConfig();
        $externalRouteId = $stopTimetable->getExternalRouteId();
        $season = $lineConfig->getSeason();
        $perimeter = $season->getPerimeter();
        $routeEnhancedStopPoints = $this->getRouteEnhancedStopPoints($perimeter, $externalRouteId, $stopTimetable);
        $payloads = array();
        foreach ($routeEnhancedStopPoints as $enhancedStopPoint) {
            if (in_array($enhancedStopPoint->stop_point->id, $stopPointsExternalIds)) {
                $payloads[] = $this->getPayload(
                    $perimeter,
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
        $perimeter = $season->getPerimeter();
        foreach ($season->getLineConfigs() as $lineConfig) {
            $externalLineId = $lineConfig->getExternalLineId();
            $routes = $this->navitia->getLineRoutes(
                $perimeter->getExternalCoverageId(),
                $perimeter->getExternalNetworkId(),
                $externalLineId
            );
            foreach ($routes as $route) {
                $stopTimetable = $this->stopTimetableManager->findStopTimetableByExternalRouteIdAndLineConfig(
                    $route->id,
                    $lineConfig
                );
                $stopPoints = $this->getRouteEnhancedStopPoints($perimeter, $route->id, $stopTimetable);
                foreach ($stopPoints as $stopPoint) {
                    $payloads[] = $this->getPayload(
                        $perimeter,
                        $season,
                        $lineConfig,
                        $route->id,
                        $stopPoint->stop_point
                    );
                }
            }
        }
        if (empty($payloads)) {
            throw new \Exception(
                $this->translator->trans(
                    'season.pdf.empty',
                    array('%seasonName%' => $season->getTitle()),
                    'default'
                )
            );
        }

        return $payloads;
    }

    public function getAreaPdfPayloads($areaPdf)
    {
        $payloads = array();
        $area = $areaPdf->getArea();
        $perimeter = $area->getPerimeter();

        foreach ($area->getStopPointsOrderByExternalRouteIdAndExternalLineId() as $externalRouteId => $areaLine) {
            foreach ($areaLine as $externalLineId => $areaStopPoints) {
                try {
                    $lineConfig = $this->lineManager->getLineConfigByExternalLineIdAndSeasonId(
                        $externalLineId,
                        $areaPdf->getSeason()->getId()
                    );
                } catch (NotFoundHttpException $e) {
                    $this->logger->addInfo('One pdf in area (' . $area->getId() . ') was not generated.');
                    continue ;
                }
                $stopTimetable = $this->stopTimetableManager->getStopTimetable(
                    $externalRouteId,
                    $perimeter->getExternalCoverageId(),
                    $lineConfig
                );

                $routeEnhancedStopPoints = $this->getRouteEnhancedStopPoints($perimeter, $externalRouteId, $stopTimetable);
                foreach ($routeEnhancedStopPoints as $routeEnhancedStopPoint) {
                    if (in_array($routeEnhancedStopPoint->stop_point->id, $areaStopPoints)) {
                        $payloads[] = $this->getPayload(
                            $perimeter,
                            $stopTimetable->getLineConfig()->getSeason(),
                            $stopTimetable->getLineConfig(),
                            $externalRouteId,
                            $routeEnhancedStopPoint->stop_point
                        );
                    }
                }
            }
        }

        if (empty($payloads)) {
            throw new \Exception(
                $this->translator->trans(
                    'area.pdf.empty',
                    array(
                        '%areaName%' => $area->getLabel(),
                        '%seasonName%' => $areaPdf->getSeason()->getTitle()
                    ),
                    'default'
                )
            );
        }

        return $payloads;
    }
}
