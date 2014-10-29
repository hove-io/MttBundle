<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MttBundle\Entity\DistributionList;

class DistributionController extends AbstractController
{
    public function saveAction(
        $externalNetworkId,
        $lineId,
        $routeId,
        $currentSeasonId
    )
    {
        $stopPointsIds = $this->get('request')->request->get(
            'stopPointsIds',
            array()
        );
        if (!empty($stopPointsIds)) {
            $lineManager = $this->get('canal_tp_mtt.line_manager');
            $networkManager = $this->get('canal_tp_mtt.network_manager');
            $network = $networkManager->findOneByExternalId($externalNetworkId);
            $timetable = $this
                ->get('canal_tp_mtt.timetable_manager')
                ->getTimetable(
                    $routeId,
                    $network->getExternalCoverageId(),
                    $lineManager->getLineConfigByExternalLineIdAndSeasonId(
                        $lineId,
                        $currentSeasonId
                    )
                );
            $this->saveList($timetable, $stopPointsIds);
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'distribution.confirm_order_saved',
                    array(),
                    'default'
                )
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_distribution_list',
                array(
                    'externalNetworkId' => $externalNetworkId,
                    'lineId' => $lineId,
                    'routeId' => $routeId,
                    'currentSeasonId' => $currentSeasonId
                )
            )
        );
    }

    public function listAction(
        $externalNetworkId,
        $lineId,
        $routeId,
        $currentSeasonId,
        $reset = false
    )
    {
        $this->isGranted('BUSINESS_MANAGE_DISTRIBUTION_LIST');
        $navitia = $this->get('sam_navitia');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $distributionListManager = $this->get('canal_tp.mtt.distribution_list_manager');

        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $routes = $navitia->getStopPoints(
            $network->getExternalCoverageId(),
            $externalNetworkId,
            $lineId,
            $routeId
        );
        $timetable = $this
            ->get('canal_tp_mtt.timetable_manager')
            ->getTimetable(
                $routeId,
                $network->getExternalCoverageId(),
                $lineManager->getLineConfigByExternalLineIdAndSeasonId(
                    $lineId,
                    $currentSeasonId
                )
            );

        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        $schedules = $stopPointManager->enhanceStopPoints(
            $routes->route_schedules[0]->table->rows,
            $timetable
        );
        $schedules = $this
            ->getDoctrine()
            ->getRepository('CanalTPMttBundle:DistributionList')
            ->sortSchedules($schedules, $network->getId(), $routeId, $reset);

        $locked = $this
            ->getDoctrine()
            ->getRepository('CanalTPMttBundle:Timetable')
            ->hasAmqpTasksRunning($timetable->getId());

        return $this->render(
            'CanalTPMttBundle:Distribution:list.html.twig',
            array(
                'pageTitle'         => $this->get('translator')->trans(
                    'distribution.generation_title',
                    array(),
                    'default'
                ),
                'timetable'         => $timetable,
                'locked'            => $locked,
                'schedules'         => $schedules,
                'current_route'     => $routeId,
                'display_informations'=> $routes->route_schedules[0]->display_informations,
                'currentNetwork'    => $network,
                'externalNetworkId' => $externalNetworkId,
                'seasons'           => $network->getSeasons(),
                'currentSeason'     => $timetable->getLineConfig()->getSeason(),
                'currentSeasonId'   => $timetable->getLineConfig()->getSeason()->getId(),
                'externalLineId'    => $lineId,
                'externalRouteId'   => $routeId,
                'pdfUrl'            => $distributionListManager->findPdfPathByTimetable($timetable)
            )
        );
    }

    public function generateAction($timetableId, $externalNetworkId)
    {
        $this->isGranted('BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $pdfPayloadGenerator = $this->get('canal_tp_mtt.pdf_payload_generator');
        $amqpPdfGenPublisher = $this->get('canal_tp_mtt.amqp_pdf_gen_publisher');
        $distributionListManager = $this->get('canal_tp.mtt.distribution_list_manager');

        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->get('canal_tp_mtt.timetable_manager')->getTimetableById(
            $timetableId,
            $network->getExternalCoverageId()
        );
        $stopPointsIds = $this->get('request')->request->get(
            'stopPointsIds', array()
        );
        $payloads = $pdfPayloadGenerator->getStopPointsPayloads($timetable, $stopPointsIds);
        if (count($payloads) > 0) {

            $distributionList = $this->saveList($timetable, $stopPointsIds);
            $task = $amqpPdfGenPublisher->publishDistributionListPdfGen($payloads, $timetable);
            $distributionListManager->deleteDistributionListPdf($timetable);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'distribution.pdf_generation_task_has_started',
                    array(
                        '%countPdfs%' => count($payloads)
                    ),
                    'default'
                )
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'danger',
                $this->get('translator')->trans(
                    'controller.distribution.generate.no_pdfs',
                    array(
                        '%count_jobs%' => count($payloads)
                    ),
                    'default'
                )
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_distribution_list',
                array(
                    'externalNetworkId' => $externalNetworkId,
                    'lineId' => $timetable->getLineConfig()->getExternalLineId(),
                    'routeId' => $timetable->getExternalRouteId(),
                    'currentSeasonId' => $timetable->getLineConfig()->getSeason()->getId()
                )
            )
        );
    }

    private function saveList($timetable, $stopPointsIncluded)
    {
        $distributionListManager = $this->get('canal_tp.mtt.distribution_list_manager');
        $distribList = $this->getDoctrine()->getRepository('CanalTPMttBundle:DistributionList');
        $distribListInstance = $distributionListManager->findByTimetable($timetable);

        if (empty($distribListInstance)) {
            $distribListInstance = new DistributionList();
            $distribListInstance->setNetwork($timetable->getLineConfig()->getSeason()->getPerimeter());
            $distribListInstance->setExternalRouteId($timetable->getExternalRouteId());
            $this->getDoctrine()->getManager()->persist($distribListInstance);
        }
        $distribListInstance->setIncludedStops($stopPointsIncluded);
        $this->getDoctrine()->getManager()->flush();

        return $distribListInstance;
    }
}
