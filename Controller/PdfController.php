<?php

namespace CanalTP\MttBundle\Controller;

class PdfController extends AbstractController
{
    /**
     * Downloading the PDF file relative to a stopPoint
     *
     * @param integer $lineConfigId
     * @param string $externalRouteId
     * @param string $externalStopPointId
     */
    public function downloadAction($externalRouteId, $externalStopPointId, $lineConfigId)
    {
        $mediaManager = $this->get('canal_tp_mtt.media_manager');
        $stopTimetableManager = $this->get('canal_tp_mtt.stop_timetable_manager');

        $media = $mediaManager->getStopPointStopTimetableMedia(
            $stopTimetableManager->findStopTimetableByExternalRouteIdAndLineConfigId($externalRouteId, $lineConfigId),
            $externalStopPointId
        );

        return $this->redirect($mediaManager->getUrlByMedia($media));
    }

    /**
     * Launching the PDF file generation for a specific stopPoint
     *
     * @param integer $stopTimetableId
     * @param string $externalNetworkId
     * @param string $externalStopPointId
     */
    public function generateAction($stopTimetableId, $externalNetworkId, $externalStopPointId)
    {
        $this->isGranted('BUSINESS_GENERATE_PDF');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $customer = $this->getUser()->getCustomer();
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );
        $stopTimetable = $this->get('canal_tp_mtt.stop_timetable_manager')->getStopTimetableById(
            $stopTimetableId,
            $perimeter->getExternalCoverageId()
        );
        $pdfManager = $this->get('canal_tp_mtt.pdf_manager');
        if ($stopTimetable->isLocked()) {
            $url = $this->generateUrl(
                'canal_tp_mtt_stop_timetable_view',
                array(
                    'externalNetworkId'     => $externalNetworkId,
                    'seasonId'              => $stopTimetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                    'externalRouteId'       => $stopTimetable->getExternalRouteId(),
                    'externalStopPointId'   => $externalStopPointId,
                    'orientation'           => $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getOrientationAsString(),
                    'customerId'            => $customer->getId(),
                )
            );
        } else {
            $url = $pdfManager->getStoppointPdfUrl($stopTimetable, $externalStopPointId);
        }

        return $this->redirect($url);
    }
}
