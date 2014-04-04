<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\Entity\Line;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MediaManagerBundle\Entity\Category;

class TimetableController extends AbstractController
{
    private $mediaManager;

    /**
     * @function retrieve a timetable entity
     */
    private function getTimetable($routeExternalId, $externalCoverageId, $lineConfig)
    {
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');

        return $timetableManager->getTimetable($routeExternalId, $externalCoverageId, $lineConfig);
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
        $this->mediaManager = $this->get('canal_tp.media_manager');
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');
        $timetable = $timetableManager->find($timetableId);

        $timetableCategory = new Category($timetableId, CategoryType::NETWORK);
        $networkCategory = new Category($timetable->getLineConfig()->getSeason()->getNetwork()->getexternalId(), CategoryType::NETWORK);
        $seasonCategory = new Category($timetable->getLineConfig()->getSeason()->getId(), CategoryType::LINE);
        $media = new Media();

        $timetableCategory->setParent($networkCategory);
        $networkCategory->setParent($seasonCategory);
        $media->setCategory($timetableCategory);
        $media->setFileName($externalStopPointId);
        $media->setFile(new File($path));
        $this->mediaManager->save($media);

        return ($media);
    }

    private function renderLayout($timetable, $externalStopPointId, $editable = true, $displayMenu = true)
    {
        $layoutsConfig = $this->container->getParameter('layouts');
        $externalCoverageId = $timetable->getLineConfig()->getSeason()->getNetwork()->getExternalCoverageId();
        
        $stopPointData = $this->getStopPoint(
            $externalStopPointId, 
            $timetable, 
            $externalCoverageId
        );
        
        $calendarsAndNotes = $this->get('canal_tp_mtt.calendar_manager')->getCalendars(
            $externalCoverageId,
            $timetable,
            $stopPointData['stopPointInstance']
        );

        return $this->render(
            'CanalTPMttBundle:Layouts:' . $timetable->getLineConfig()->getTwigPath(),
            array(
                'timetable'             => $timetable,
                'externalNetworkId'     => $timetable->getLineConfig()->getSeason()->getNetwork()->getExternalId(),
                'externalRouteId'       => $timetable->getExternalRouteId(),
                'externalCoverageId'    => $externalCoverageId,
                'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                'season'                => $timetable->getLineConfig()->getSeason(),
                'stopPointLevel'        => $stopPointData['stopPointLevel'],
                'stopPoint'             => $stopPointData['stopPointInstance'],
                'calendars'             => $calendarsAndNotes['calendars'],
                'notes'                 => $calendarsAndNotes['notes'],
                'blockTypes'            => $this->container->getParameter('blocks'),
                'layoutConfig'          => $layoutsConfig[$timetable->getLineConfig()->getLayout()],
                'layout'                => $timetable->getLineConfig()->getLayout(),
                'editable'              => $editable,
                'displayMenu'           => $displayMenu
            )
        );
    }
    
    /*
     * Display a layout and make it editable via javascript
     */
    public function editAction($externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->getTimetable(
            $externalRouteId,
            $network->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );
        
        return $this->renderLayout($timetable, $externalStopPointId, true, true);
    }
    
    /*
     * Display a layout
     * This action needs to be accessible by an anonymous user
     */
    public function viewAction($externalNetworkId, $externalRouteId, $externalLineId, $seasonId, $externalStopPointId = null)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $lineManager = $this->get('canal_tp_mtt.line_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->getTimetable(
            $externalRouteId,
            $network->getExternalCoverageId(),
            $lineManager->getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
        );
        $displayMenu = ($this->get('security.context')->getToken()->getUser() != 'anon.');

        return $this->renderLayout($timetable, $externalStopPointId, false, $displayMenu);
    }

    public function generatePdfAction($timetableId, $externalNetworkId, $externalStopPointId)
    {
        $this->isGranted('BUSINESS_GENERATE_PDF');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->get('canal_tp_mtt.timetable_manager')->getTimetableById(
            $timetableId,
            $network->getExternalCoverageId()
        );
        $pdfGenerator = $this->get('canal_tp_mtt.pdf_generator');

        $url = $this->get('request')->getHttpHost() . $this->get('router')->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => $externalNetworkId,
                'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                'externalStopPointId'=> $externalStopPointId,
                'externalRouteId'    => $timetable->getExternalRouteId()
            )
        );
        $pdfPath = $pdfGenerator->getPdf($url, $timetable->getLineConfig()->getLayout());

        if ($pdfPath) {
            $pdfMedia = $this->saveMedia($timetable->getId(), $externalStopPointId, $pdfPath);
            $this->getDoctrine()->getRepository('CanalTPMttBundle:StopPoint')->updatePdfGenerationDate($externalStopPointId, $timetable);

            return $this->redirect($this->mediaManager->getUrlByMedia($pdfMedia));
        } else {
            throw new Exception('PdfGenerator Webservice returned an empty response.');
        }

    }
}
