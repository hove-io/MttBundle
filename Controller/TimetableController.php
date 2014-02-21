<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
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

    private function getStopPoint($externalStopPointId, $externalRouteId, $externalCoverageId)
    {
        // are we on a specific stop_point 
        if ($externalStopPointId != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint($externalStopPointId, $externalCoverageId);
            $calendars = $this->get('canal_tp_meth.calendar_manager')->getCalendarsForStopPoint(
                $externalCoverageId, 
                $externalRouteId, 
                $externalStopPointId
            );
        } else {
            $stopPointLevel = false;
            $stopPointInstance = false;
            $calendars = $this->get('canal_tp_meth.calendar_manager')->getCalendarsForRoute(
                $externalCoverageId, 
                $externalRouteId
            );
        }

        return array(
            'stopPointLevel'    => $stopPointLevel,
            'stopPointInstance' => $stopPointInstance,
            'calendars'         => $calendars
        );
    }

    private function saveMedia($timetableId, $externalStopPointId, $path)
    {
        $this->mediaManager = $this->get('canaltp_media_manager_mtt');
        $media = new Media(
            CategoryType::LINE,
            $timetableId,
            CategoryType::STOP_POINT,
            $externalStopPointId
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
        $stopPointData = $this->getStopPoint($externalStopPointId, $externalRouteId, $externalCoverageId);

        return $this->render(
            'CanalTPMethBundle:Layouts:' . $timetable->getLine()->getTwigPath(),
            array(
                'timetable'             => $timetable,
                'externalCoverageId'    => $externalCoverageId,
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'calendars'             => $stopPointData['calendars'],
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
        $stopPointData = $this->getStopPoint($externalStopPointId, $externalRouteId, $externalCoverageId);

        return $this->render(
            'CanalTPMethBundle:Layouts:' .  $timetable->getLine()->getTwigPath(),
            array(
                'timetable'         => $timetable,
                'externalCoverageId'=> $externalCoverageId,
                'stopPointLevel'    => $stopPointData['stopPointLevel'],
                'stopPoint'         => $stopPointData['stopPointInstance'],
                'calendars'         => $stopPointData['calendars'],
                'editable'          => false
            )
        );
    }

    public function generatePdfAction($timetableId, $externalCoverageId, $externalStopPointId)
    {
        $timetable = $this->get('canal_tp_meth.timetable_manager')->getTimetableById($timetableId, $externalCoverageId);
        $pdfGenerator = $this->get('canal_tp_meth.pdf_generator');

        $url = $this->get('request')->getHttpHost() . $this->get('router')->generate(
            'canal_tp_meth_timetable_view',
            array(
                'externalCoverageId' => $externalCoverageId,
                'externalStopPointId'=> $externalStopPointId,
                'externalRouteId'    => $timetable->getExternalRouteId()
            )
        );
        $pdfPath = $pdfGenerator->getPdf($url, $timetable->getLine()->getLayout());

        if ($pdfPath) {
            $pdfMedia = $this->saveMedia($timetable->getId(), $externalStopPointId, $pdfPath);
            $this->getDoctrine()->getRepository('CanalTPMethBundle:StopPoint', 'mtt')->updatePdfGenerationDate($externalStopPointId);

            return $this->redirect($this->mediaManager->getUrlByMedia($pdfMedia));
        } else {
            throw new Exception('PdfGenerator Webservice returned an empty response.');
        }

    }
}
