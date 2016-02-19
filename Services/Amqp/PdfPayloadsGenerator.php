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
use CanalTP\MttBundle\Services\TimetableManager;
use CanalTP\MttBundle\Services\StopPointManager;
use CanalTP\MttBundle\Services\LineManager;

class PdfPayloadsGenerator
{
    private $co = null;
    private $router = null;
    private $navitia = null;
    private $timetableManager = null;
    private $stopPointManager = null;
    private $lineManager = null;
    private $logger = null;

    public function __construct(
        Container $co,
        Router $router,
        Navitia $navitia,
        TimetableManager $timetableManager,
        StopPointManager $stopPointManager,
        LineManager $lineManager,
        Logger $logger,
        TranslatorInterface $translator
    ) {
        $this->co = $co;
        $this->logger = $logger;
        $this->router = $router;
        $this->navitia = $navitia;
        $this->timetableManager = $timetableManager;
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
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId'     => $perimeter->getExternalNetworkId(),
                'externalLineId'        => $lineConfig->getExternalLineId(),
                'externalRouteId'       => $externalRouteId,
                'seasonId'              => $season->getId(),
                'externalStopPointId'   => $stopPoint->id,
                'customerId'            => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
                'timetableOnly'         => true
            )
        );
        $payload['timetableParams'] = array(
            'seasonId'              => $season->getId(),
            'externalNetworkId'     => $perimeter->getExternalNetworkId(),
            'externalRouteId'       => $externalRouteId,
            'externalLineId'        => $lineConfig->getExternalLineId(),
            'externalStopPointId'   => $stopPoint->id,
        );

        return $payload;
    }

    private function getRouteEnhancedStopPoints($perimeter, $externalRouteId, $timetable)
    {
        $routeSchedulesData = $this->navitia->getRouteStopPoints($perimeter, $externalRouteId);
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
        $perimeter = $season->getPerimeter();
        $routeEnhancedStopPoints = $this->getRouteEnhancedStopPoints($perimeter, $externalRouteId, $timetable);
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
                $timetable = $this->timetableManager->findTimetableByExternalRouteIdAndLineConfig(
                    $route->id,
                    $lineConfig
                );
                $stopPoints = $this->getRouteEnhancedStopPoints($perimeter, $route->id, $timetable);
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
                $timetable = $this->timetableManager->getTimetable(
                    $externalRouteId,
                    $perimeter->getExternalCoverageId(),
                    $lineConfig
                );

                $routeEnhancedStopPoints = $this->getRouteEnhancedStopPoints($perimeter, $externalRouteId, $timetable);
                foreach ($routeEnhancedStopPoints as $routeEnhancedStopPoint) {
                    if (in_array($routeEnhancedStopPoint->stop_point->id, $areaStopPoints)) {
                        $payloads[] = $this->getPayload(
                            $perimeter,
                            $timetable->getLineConfig()->getSeason(),
                            $timetable->getLineConfig(),
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
