<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\Entity\Line;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MediaManagerBundle\Entity\Category;

class TimetableController extends Controller
{
    private $mediaManager;

    /**
     * @function retrieve a timetable entity
     */
    private function getTimetable($routeExternalId, $externalCoverageId)
    {
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');

        return $timetableManager->getTimetable($routeExternalId, $externalCoverageId);
    }

    private function getStopPoint($externalStopPointId, $timetable, $externalCoverageId)
    {
        // are we on a specific stop_point
        if ($externalStopPointId != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint(
                $externalStopPointId, 
                $timetable,
                $externalCoverageId
            );
        // route level
        } else {
            $stopPointLevel = false;
            $stopPointInstance = false;
        }

        return array(
            'stopPointLevel'    => $stopPointLevel,
            'stopPointInstance' => $stopPointInstance,
        );
    }

    private function saveMedia($timetableId, $externalStopPointId, $path)
    {
        $this->mediaManager = $this->get('canaltp_media_manager_mtt');

        $stopPointCategory = new Category($externalStopPointId, CategoryType::STOP_POINT);
        $lineCategory = new Category($timetableId, CategoryType::LINE);
        $media = new Media();

        $stopPointCategory->setParent($lineCategory);
        $media->setCategory($stopPointCategory);
        $media->setFileName($externalStopPointId);
        $media->setFile(new File($path));
        $this->mediaManager->save($media);

        return ($media);
    }

    /*
     * Display a layout and make editable via javascript
     */
    public function editAction($externalNetworkId, $externalRouteId, $externalStopPointId = null)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->getTimetable(
            $externalRouteId,
            $network->getExternalCoverageId()
        );
        $stopPointData = $this->getStopPoint(
            $externalStopPointId, 
            $timetable, 
            $network->getExternalCoverageId()
        );
        $calendarsAndNotes = $this->get('canal_tp_mtt.calendar_manager')->getCalendars(
            $network->getExternalCoverageId(),
            $timetable,
            $stopPointData['stopPointInstance']
        );
        $layoutsConfig = $this->container->getParameter('layouts');
        
        return $this->render(
            'CanalTPMttBundle:Layouts:' . $timetable->getLine()->getTwigPath(),
            array(
                'timetable'             => $timetable,
                'externalNetworkId'     => $externalNetworkId,
                'externalCoverageId'    => $network->getExternalCoverageId(),
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'calendars'             => $calendarsAndNotes['calendars'],
                'notes'                 => $calendarsAndNotes['notes'],
                'blockTypes'            => $this->container->getParameter('blocks'),
                'layoutConfig'          => $layoutsConfig[$timetable->getLine()->getLayout()],
                'layout'                => $timetable->getLine()->getLayout(),
                'editable'              => true
            )
        );
    }

    /*
     * Display a layout
     */
    public function viewAction($externalNetworkId, $externalRouteId, $externalStopPointId = null)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->getTimetable($externalRouteId, $network->getExternalCoverageId());
        $stopPointData = $this->getStopPoint(
            $externalStopPointId, 
            $timetable, 
            $network->getExternalCoverageId()
        );
        $calendarsAndNotes = $this->get('canal_tp_mtt.calendar_manager')->getCalendars(
            $network->getExternalCoverageId(),
            $timetable,
            $stopPointData['stopPointInstance']
        );
        $layoutsConfig = $this->container->getParameter('layouts');
        return $this->render(
            'CanalTPMttBundle:Layouts:' .  $timetable->getLine()->getTwigPath(),
            array(
                'timetable'         => $timetable,
                'externalNetworkId' => $externalNetworkId,
                'externalCoverageId'=> $network->getExternalCoverageId(),
                'stopPointLevel'    => $stopPointData['stopPointLevel'],
                'stopPoint'         => $stopPointData['stopPointInstance'],
                'calendars'         => $calendarsAndNotes['calendars'],
                'notes'             => $calendarsAndNotes['notes'],
                'layoutConfig'      => $layoutsConfig[$timetable->getLine()->getLayout()],
                'layout'            => $timetable->getLine()->getLayout(),
                'editable'          => false
            )
        );
    }

    public function generatePdfAction($timetableId, $externalNetworkId, $externalStopPointId)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->get('canal_tp_mtt.timetable_manager')->getTimetableById(
            $timetableId,
            $network->getExternalCoverageId()
        );
        $pdfGenerator = $this->get('canal_tp_mtt.pdf_generator');

        $url = $this->get('request')->getHttpHost() . $this->get('router')->generate(
            'canal_tp_meth_timetable_view',
            array(
                'externalNetworkId' => $externalNetworkId,
                'externalStopPointId'=> $externalStopPointId,
                'externalRouteId'    => $timetable->getExternalRouteId()
            )
        );
        $pdfPath = $pdfGenerator->getPdf($url, $timetable->getLine()->getLayout());

        if ($pdfPath) {
            $pdfMedia = $this->saveMedia($timetable->getId(), $externalStopPointId, $pdfPath);
            $this->getDoctrine()->getRepository('CanalTPMttBundle:StopPoint')->updatePdfGenerationDate($externalStopPointId);

            return $this->redirect($this->mediaManager->getUrlByMedia($pdfMedia));
        } else {
            throw new Exception('PdfGenerator Webservice returned an empty response.');
        }

    }
}
