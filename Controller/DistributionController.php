<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\Entity\DistributionList;

class DistributionController extends Controller
{
    public function listAction($externalNetworkId, $lineId, $routeId)
    {
        $navitia = $this->get('iussaad_navitia');
        $networkManager = $this->get('canal_tp_mtt.network_manager');

        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $routes = $navitia->getStopPoints($network->getExternalCoverageId(), $externalNetworkId, $lineId, $routeId);
        $timetable = $this
            ->get('canal_tp_mtt.timetable_manager')
            ->getTimetable($routeId, $network->getExternalCoverageId());

        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        $schedules = $stopPointManager->enhanceStopPoints($routes->route_schedules[0]->table->rows);
        $schedules = $this
            ->getDoctrine()
            ->getRepository('CanalTPMttBundle:DistributionList')
            ->sortSchedules($schedules, $timetable);

        return $this->render(
            'CanalTPMttBundle:Distribution:list.html.twig',
            array(
                'timetable'     => $timetable,
                'schedules'     => $schedules,
                'current_route' => $routeId,
                'coverage_id'   => $network->getExternalCoverageId(),
                'network_id'    => $externalNetworkId,
                'line_id'       => $lineId,
                'route_id'      => $routeId,
            )
        );
    }

    public function generateAction($timetableId, $externalCoverageId)
    {
        $timetable = $this->get('canal_tp_mtt.timetable_manager')->getTimetableById($timetableId, $externalCoverageId);
        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        $stopPointRepo = $this->getDoctrine()->getRepository('CanalTPMttBundle:StopPoint');
        $this->mediaManager = $this->get('canaltp_media_manager_mtt');

        $stopPointsIds = $this->get('request')->request->get('stopPointsIds', array());
        $paths = array();
        foreach ($stopPointsIds as $externalStopPointId) {
            $stopPoint = $stopPointManager->getStopPoint($externalStopPointId, $timetable, $externalCoverageId);
            //shall we regenerate pdf?
            if ($stopPointRepo->hasPdfUpToDate($stopPoint, $timetable) == false) {
                $response = $this->forward(
                    'CanalTPMttBundle:Timetable:generatePdf',
                    array(
                        'timetableId'           => $timetableId,
                        'externalCoverageId'    => $externalCoverageId,
                        'externalStopPointId'   => $externalStopPointId,
                    )
                );
            }

            $stopPointCategory = new Category($externalStopPointId, CategoryType::STOP_POINT);
            $lineCategory = new Category($timetableId, CategoryType::LINE);
            $media = new Media();

            $stopPointCategory->setParent($lineCategory);
            $media->setCategory($stopPointCategory);
            $media->setFileName($externalStopPointId);
            $paths[] = $this->mediaManager->getPathByMedia($media);
        }

        if (count($paths) > 0) {
            // save this list in db
            $this->saveList($timetable, $stopPointsIds);
            $pdfGenerator = $this->get('canal_tp_mtt.pdf_generator');
            $filePath = $pdfGenerator->aggregatePdf($paths);

            return new JsonResponse(
                array(
                    'path' => $this->getRequest()->getBasePath() . $filePath
                )
            );
        } else {
            throw new \Exception(
                $this->get('translator')->trans(
                    'controller.distribution.generate.no_pdfs', 
                    array(), 
                    'exceptions'
                )
            );
        }
    }

    private function saveList($timetable, $stopPointsIncluded)
    {
        $distribList = $this->getDoctrine()->getRepository('CanalTPMttBundle:DistributionList');
        $distribListInstance = $distribList->findOneByTimetable($timetable);
        if (empty($distribListInstance)) {
            $distribListInstance = new DistributionList();
            $distribListInstance->setTimetable($timetable);
        }
        $distribListInstance->setIncludedStops($stopPointsIncluded);
        $this->getDoctrine()->getManager()->persist($distribListInstance);
        $this->getDoctrine()->getManager()->flush();
    }
}
