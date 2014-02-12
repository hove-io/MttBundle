<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MediaManager\Category\CategoryType;

class DistributionController extends Controller
{
    public function listAction($coverageId, $networkId, $lineId, $routeId)
    {
        $navitia = $this->get('iussaad_navitia');
        $routes = $navitia->getStopPoints($coverageId, $networkId, $lineId, $routeId);

        $line = $this->getDoctrine()->getRepository(
            'CanalTPMethBundle:Line',
            'mtt'
        )->findOneBy(
            array(
                'coverageId'    => $coverageId,
                'networkId'     => $networkId,
                'navitiaId'     => $lineId,
            )
        );

        $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
        $routes->route_schedules[0]->table->rows = $stopPointManager->enhanceStopPoints($routes->route_schedules[0]->table->rows, $line);
        
        return $this->render(
            'CanalTPMethBundle:Distribution:list.html.twig',
            array(
                'line'          => $line,
                'routes'        => $routes,
                'current_route' => $routeId,
                'coverage_id'   => $coverageId,
                'network_id'    => $networkId,
                'line_id'       => $lineId,
                'route_id'      => $routeId,
            )
        );
    }
    
    public function generateAction($lineId)
    {
        $stopPointsIds = $this->get('request')->request->get('stopPointsIds');
        // var_dump($stopPointsIds);die;
        $stopPointRepo = $this->getDoctrine()->getRepository('CanalTPMethBundle:StopPoint', 'mtt');
        $this->mediaManager = $this->get('canaltp_media_manager_mtt');
        $paths = array();
        foreach ($stopPointsIds as $stopPointId)
        {
            //shall we regenerate pdf?
            if ($stopPointRepo->hasPdfUpToDate($stopPointId, $lineId) == false)
            {
                $response = $this->forward('CanalTPMethBundle:Timetable:generatePdf', array(
                    'lineId'  => $lineId,
                    'stopPointId' => $stopPointId,
                ));
                // var_dump($response);
            }
            $media = new Media(
                CategoryType::LINE,
                $lineId,
                CategoryType::STOP_POINT,
                $stopPointId
            );
            $paths[] = $this->mediaManager->getPathByMedia($media);
        }
        $pdfGenerator = $this->get('canal_tp_meth.pdf_generator');
        $filePath = $pdfGenerator->aggregatePdf($paths);
        return new JsonResponse(array('path' => $this->getRequest()->getBasePath() . $filePath));
    }
}
