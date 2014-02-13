<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MethBundle\Entity\Line;
use CanalTP\MediaManagerBundle\Entity\Media;

class TimetableController extends Controller
{
    private $mediaManager;
    
    /*
     * @function retrieve a route by a route navitia id
     */
    private function getRoute($externalCoverageId, $routeExternalId)
    {
        $routeManager = $this->get('canal_tp_meth.route_manager');
        return $routeManager->getRoute($routeExternalId, $externalCoverageId);
    }
    
    private function getStopPoint($stopPointExternalId)
    {
        // are we on stop_point level?
        if ($stopPointExternalId != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint($stopPointExternalId);
        } else {
            $stopPointLevel = false;
            $stopPointInstance = false;
        }
        return array(
            'stopPointLevel' => $stopPointLevel,
            'stopPointInstance' => $stopPointInstance
        );
    }
    
    /*
     * Display a layout
     */
    public function viewAction($line_id, $stopPoint = null)
    {
        $line = $this->getLine($line_id);
        $stopPointData = $this->getStopPoint($line, $stopPoint);

        return $this->render(
            'CanalTPMethBundle:Layouts:' . $line->getTwigPath(),
            array(
                'line'            => $line,
                'stopPointLevel'  => $stopPointData['stopPointLevel'],
                'stopPoint'       => $stopPointData['stopPointInstance'],
                'editable'        => false
            )
        );
    }
    
    /*
     * @function Display a layout and make editable via javascript
     */
    public function editAction($externalCoverageId, $routeExternalId, $stopPoint = null)
    {
        $route = $this->getRoute($externalCoverageId, $routeExternalId);
        $stopPointData = $this->getStopPoint($stopPoint);
        
        return $this->render(
            'CanalTPMethBundle:Layouts:' . $route->getLine()->getTwigPath(),
            array(
                'route'                 => $route,
                'externalCoverageId'    => $externalCoverageId,
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'blockTypes'            => $this->container->getParameter('blocks'),
                'editable'              => true
            )
        );
    }

    private function save($lineId, $stopPointId, $path)
    {
        $this->mediaManager = $this->get('canaltp_media_manager_mtt');
        $media = new Media(
            CategoryType::LINE,
            $lineId,
            CategoryType::STOP_POINT,
            $stopPointId
        );

        $media->setFile(new File($path));
        $this->mediaManager->save($media);
        return ($media);
    }

    public function generatePdfAction($lineId, $stopPointId)
    {
        $line = $this->getLine($lineId);
        $pdfGenerator = $this->get('canal_tp_meth.pdf_generator');
        
        $url = 
            $this->get('request')->getHttpHost() . 
            $this->get('router')->generate(
                'canal_tp_meth_timetable_view', 
                array(
                    'line_id' => $lineId, 
                    'stopPoint' => $stopPointId
                )
            )
        ;
        $pdfPath = $pdfGenerator->getPdf($url, $line->getLayout());
        if ($pdfPath)
        {
            $this->getDoctrine()->getRepository('CanalTPMethBundle:StopPoint', 'mtt')->updatePdfGenerationDate($lineId, $stopPointId);
            $pdfMedia = $this->save($lineId, $stopPointId, $pdfPath);

            return $this->redirect($this->mediaManager->getUrlByMedia($pdfMedia));
        }
        else
        {
            throw new Exception('PdfGenerator Webservice gave an emtpy response.');
        }
        
    }
}
