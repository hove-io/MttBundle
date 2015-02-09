<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\CanalTPMttBundle;

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

    public function generateAction($objectType, $objectId, $externalNetworkId, $externalStopPointId)
    {
        $this->isGranted('BUSINESS_GENERATE_PDF');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $customer = $this->getUser()->getCustomer();
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );

        $pdfManager = $this->get('canal_tp_mtt.pdf_manager');


        switch($objectType) {
            case 'timetable':
                $timetable = $this->get('canal_tp_mtt.timetable_manager')->getById(
                    $objectId,
                    $perimeter->getExternalCoverageId()
                );

                if ($timetable->isLocked()) {
                    $url = $this->generateUrl(
                        'canal_tp_mtt_timetable_view',
                        array(
                            'externalNetworkId'     => $externalNetworkId,
                            'seasonId'              => $timetable->getLineConfig()->getSeason()->getId(),
                            'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                            'externalRouteId'       => $timetable->getExternalRouteId(),
                            'externalStopPointId'   => $externalStopPointId,
                            'customerId'            => $customer->getId()
                        )
                    );
                } else {
                    $url = $pdfManager->getStoppointPdfUrl($timetable, $externalStopPointId);
                }

                break;

            case 'lineTimecard':

                $lineTimecard = $this->get('canal_tp_mtt.line_timecard_manager')->getById(
                    $objectId,
                    $perimeter->getExternalCoverageId()
                );

                if ($lineTimecard->isLocked()) {
                    $url = $this->generateUrl(
                        'canal_tp_mtt_timecard_view',
                        array(
                            'externalNetworkId'     => $externalNetworkId,
                            'externalLineId'        => $lineTimecard->getLineConfig()->getExternalLineId()
                        )
                    );
                } else {
                    $url = $pdfManager->getStoppointPdfUrl($lineTimecard, $externalStopPointId);
                }

                break;

            default:
                throw new Exception('Object not supported');
                break;
        }
        return $this->redirect($url);
    }

}
