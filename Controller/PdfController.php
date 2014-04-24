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
        return $this->redirect(
            $pdfManager->getStoppointPdfUrl($timetable, $externalStopPointId)
        );
    }
}
