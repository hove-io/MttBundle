<?php

/**
 * Description of PdfManager
 *
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\StopPoint;
use CanalTP\MttBundle\Entity\StopTimetable;

class PdfManager
{
    private $router = null;
    private $stopPoint = null;
    private $pdfGenerator = null;
    private $mediaManager = null;
    private $co = null;
    private $hashingLib = null;

    public function __construct(
        ObjectManager $om,
        Router $router,
        PdfGenerator $pdfGenerator,
        MediaManager $mediaManager,
        Container $co,
        PdfHashingLib $hashingLib
    )
    {
        $this->stopPoint = $om->getRepository('CanalTPMttBundle:StopPoint');
        $this->router = $router;
        $this->pdfGenerator = $pdfGenerator;
        $this->mediaManager = $mediaManager;
        $this->co = $co;
        $this->hashingLib = $hashingLib;
    }

    /**
     * We use http kernel to forward, would take more time using curl
     */
    public function getStopTimetableHtml($args)
    {
        $args['_controller'] = 'CanalTPMttBundle:StopTimetable:view';
        $subRequest = $this->co->get('request')->duplicate(array(), null, $args);
        $subRequest->headers->remove('X-Requested-With');

        return $this->co->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST)->getContent();
    }

    public function getPdfHash(StopTimetable $stopTimetable, $externalStopPointId)
    {
        $response = $this->getStopTimetableHtml(
            array(
                'externalNetworkId'     => $stopTimetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'seasonId'              => $stopTimetable->getLineConfig()->getSeason()->getId(),
                'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                'externalStopPointId'   => $externalStopPointId,
                'externalRouteId'       => $stopTimetable->getExternalRouteId(),
                'customerId'            => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
                'stopTimetableOnly'         => true
            )
        );
        $cssVersion = $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getCssVersion();

        return $this->hashingLib->getPdfHash($response, $cssVersion);
    }

    private function getCurrentHttpHost()
    {
        $url = null;

        if ($this->co->hasParameter('canal_tp_mtt.payload_host')) {
            $url = 'http://' . $this->co->getParameter('canal_tp_mtt.payload_host');
        } else {
            $url = $this->co->get('request')->getHttpHost();
        }

        return ($url);
    }

    public function getStoppointPdfUrl(StopTimetable $stopTimetable, $externalStopPointId)
    {
        $hash = $this->getPdfHash($stopTimetable, $externalStopPointId);
        $stopPoint = $this->stopPoint->findOneByExternalId($externalStopPointId);

        if (!empty($stopPoint) && $hash == $stopPoint->getPdfHash()) {
            $pdfMedia = $this->mediaManager->getStopPointStopTimetableMedia($stopTimetable, $externalStopPointId);
        } else {
            $url = $this->getCurrentHttpHost() . $this->co->get('router')->generate(
                'canal_tp_mtt_stop_timetable_view',
                array(
                    'externalNetworkId' => $stopTimetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                    'seasonId'          => $stopTimetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'    => $stopTimetable->getLineConfig()->getExternalLineId(),
                    'externalStopPointId'=> $externalStopPointId,
                    'externalRouteId'    => $stopTimetable->getExternalRouteId(),
                    'customerId'        => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
                    'stopTimetableOnly'     => true
                )
            );

            $pdfPath = $this->pdfGenerator->getPdf(
                $url,
                $stopTimetable->getLineConfig()->getLayoutConfig()->getLayout()->getOrientationAsString()
            );
            if ($pdfPath) {
                $pdfMedia = $this->mediaManager->saveStopPointStopTimetable($stopTimetable, $externalStopPointId, $pdfPath);
                $this->stopPoint->updatePdfGenerationInfos($externalStopPointId, $stopTimetable, $hash);
            } else {
                throw new \Exception('PdfGenerator Webservice returned an empty response.');
            }
        }

        return $this->mediaManager->getUrlByMedia($pdfMedia);
    }
}
