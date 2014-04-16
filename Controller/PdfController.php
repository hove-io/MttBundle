<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\Entity\Line;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MediaManagerBundle\Entity\Category;

class PdfController extends AbstractController
{
    private $mediaManager;
    
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
    
    public function generateAction($timetableId, $externalNetworkId, $externalStopPointId)
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