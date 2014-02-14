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
    
    /**
     * @function retrieve a timetable entity
     */
    private function getTimetable($routeExternalId, $externalCoverageId)
    {
        $timetableManager = $this->get('canal_tp_meth.timetable_manager');
        return $timetableManager->getTimetable($routeExternalId, $externalCoverageId);
    }
    
    private function getStopPoint($externalStopPointId, $externalCoverageId, $timetable)
    {
        // are we on stop_point level?
        if ($externalStopPointId != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint($externalStopPointId, $externalCoverageId, $timetable);
        } else {
            $stopPointLevel = false;
            $stopPointInstance = false;
        }
        return array(
            'stopPointLevel'    => $stopPointLevel,
            'stopPointInstance' => $stopPointInstance
        );
    }
    
    private function saveMedia($lineId, $stopPointId, $path)
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
    
    /*
     * @function Display a layout and make editable via javascript
     */
    public function editAction($externalCoverageId, $externalRouteId, $externalStopPointId = null)
    {
        $timetable = $this->getTimetable($externalRouteId, $externalCoverageId);
        $stopPointData = $this->getStopPoint($externalStopPointId, $externalCoverageId, $timetable);
        
        return $this->render(
            'CanalTPMethBundle:Layouts:' . $timetable->getLine()->getTwigPath(),
            array(
                'timetable'             => $timetable,
                'externalCoverageId'    => $externalCoverageId,
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'blockTypes'            => $this->container->getParameter('blocks'),
                'editable'              => true
            )
        );
    }
    
    /*
     * Display a layout
     */
    public function viewAction($externalCoverageId, $externalRouteId, $externalStopPointId = null)
    {
        $timetable = $this->getTimetable($externalRouteId, $externalCoverageId);
        $stopPointData = $this->getStopPoint($externalStopPointId, $externalCoverageId, $timetable);

        return $this->render(
            'CanalTPMethBundle:Layouts:' .  $timetable->getLine()->getTwigPath(),
            array(
                'timetable'       => $timetable,
                'stopPointLevel'  => $stopPointData['stopPointLevel'],
                'stopPoint'       => $stopPointData['stopPointInstance'],
                'editable'        => false
            )
        );
    }

    public function generatePdfAction($timetableId, $stopPointId)
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
            $pdfMedia = $this->saveMedia($lineId, $stopPointId, $pdfPath);

            return $this->redirect($this->mediaManager->getUrlByMedia($pdfMedia));
        }
        else
        {
            throw new Exception('PdfGenerator Webservice gave an emtpy response.');
        }
        
    }
}
