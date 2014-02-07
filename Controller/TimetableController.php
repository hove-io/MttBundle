<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MethBundle\Entity\Line;
use CanalTP\MediaManagerBundle\Entity\Media;

class TimetableController extends Controller
{
    private $mediaManager;
    
    private function getLine($line_id)
    {
        $lineManager = $this->get('canal_tp_meth.line_manager');
        return $lineManager->getLine($line_id);
    }
    
    private function getStopPoint($line, $stopPoint)
    {
        // are we on stop_point level?
        if ($stopPoint != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint($stopPoint, $line);
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
        // var_dump($stopPointData);die;
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
     * Display a layout and make editable via javascript
     */
    public function editAction($line_id, $stopPoint = null)
    {
        $line = $this->getLine($line_id);
        $stopPointData = $this->getStopPoint($line, $stopPoint);
        
        return $this->render(
            'CanalTPMethBundle:Layouts:' . $line->getTwigPath(),
            array(
                'line'            => $line,
                'stopPointLevel'  => $stopPointData['stopPointLevel'],
                'stopPoint'       => $stopPointData['stopPointInstance'],
                'blockTypes'      => $this->container->getParameter('blocks'),
                'editable'        => true
            )
        );
    }

    // save Pdf in MediaManager 
    private function save($lineId, $stopPointId, $path)
    {
        $media = new Media(
            CategoryType::LINE,
            $lineId,
            CategoryType::STOP_POINT,
            // TODO: should be done by the media manager
            str_replace(':', '_', $stopPointId)
        );
        $media->setFile(new File($path));
        $this->mediaManager = $this->get('canaltp_media_manager_mtt');
        $this->mediaManager->save($media->getFile()->getPathName(), $media->getId());
        
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
            $this->getDoctrine()->getRepository('CanalTPMethBundle:StopPoint', 'meth')->updatePdfGenerationDate($lineId, $stopPointId);
            $pdfMedia = $this->save($lineId, $stopPointId, $pdfPath);

            return $this->redirect($this->mediaManager->getUrlByMedia($pdfMedia));
        }
        else
        {
            throw new Exception('PdfGenerator Webservice gave an emtpy response.');
        }
        
    }
}
