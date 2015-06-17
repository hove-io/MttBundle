<?php

namespace CanalTP\MttBundle\Controller;

class PdfController extends AbstractController
{
    public function downloadAction($externalRouteId, $externalStopPointId, $lineConfigId)
    {
        $mediaManager = $this->get('canal_tp_mtt.media_manager');
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');

        $media = $mediaManager->getStopPointTimetableMedia(
            $timetableManager->findTimetableByExternalRouteIdAndLineConfigId($externalRouteId, $lineConfigId),
            $externalStopPointId
        );

        return $this->redirect($mediaManager->getUrlByMedia($media));
    }

    public function generateAction($timetableId, $externalNetworkId, $externalStopPointId)
    {
        $this->isGranted('BUSINESS_GENERATE_PDF');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $customer = $this->getUser()->getCustomer();
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );
        $timetable = $this->get('canal_tp_mtt.timetable_manager')->getTimetableById(
            $timetableId,
            $perimeter->getExternalCoverageId()
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
                    'externalStopPointId'   => $externalStopPointId,
                    'orientation'           => $timetable->getLineConfig()->getLayoutConfig()->getLayout()->getOrientationAsString(),
                    'customerId'            => $customer->getId(),
                )
            );
        } else {
            $url = $pdfManager->getStoppointPdfUrl($timetable, $externalStopPointId);
        }

        return $this->redirect($url);
    }
}
