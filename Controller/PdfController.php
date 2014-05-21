<?php

namespace CanalTP\MttBundle\Controller;

class PdfController extends AbstractController
{
    public function generateAction($timetableId, $externalNetworkId, $externalStopPointId)
    {
        $this->isGranted('BUSINESS_GENERATE_PDF');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetable = $this->get('canal_tp_mtt.timetable_manager')->getTimetableById(
            $timetableId,
            $network->getExternalCoverageId()
        );
        $pdfManager = $this->get('canal_tp_mtt.pdf_manager');
        if ($timetable->isLocked()) {
            $url = $this->generateUrl(
                'canal_tp_mtt_timetable_view',
                array(
                    'externalNetworkId'     => $externalNetworkId,
                    'seasonId'              => $timetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                    'externalRouteId'       => $timetable->getExternalRouteId(),
                    'externalStopPointId'   => $externalStopPointId
                )
            );
        } else {
            $url = $pdfManager->getStoppointPdfUrl($timetable, $externalStopPointId);
        }
        return $this->redirect($url);
    }
}
