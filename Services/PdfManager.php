<?php

/**
 * Description of PdfManager
 *
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\StopPoint;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\LineTimecard;

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
        $this->lineTimecardRepo = $om->getRepository('CanalTPMttBundle:LineTimecard');
        $this->router = $router;
        $this->pdfGenerator = $pdfGenerator;
        $this->mediaManager = $mediaManager;
        $this->co = $co;
        $this->hashingLib = $hashingLib;
    }

    /**
     * We use http kernel to forward, would take more time using curl
     */
    public function getHtml($args)
    {


        $args['externalNetworkId'] = $args['object']->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId();
        $args['seasonId'] = $args['object']->getLineConfig()->getSeason()->getId();
        $args['externalLineId'] = $args['object']->getLineConfig()->getExternalLineId();

        $subRequest = $this->co->get('request')->duplicate(array(), null, $args);
        $subRequest->headers->remove('X-Requested-With');

        return $this->co->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST)->getContent();
    }

    /**
     *
     * @param $object
     * @param $externalStopPointId
     * @return string
     */
    public function getPdfHash($object, $externalStopPointId)
    {
        if ($object instanceof Timetable) {
            $param = array(
                'externalRouteId' => $object->getExternalRouteId(),
                '_controller' => 'CanalTPMttBundle:Timetable:view'
            );
        } else if ($object instanceof LineTimecard) {
            $param = array(
                '_controller' => 'CanalTPMttBundle:Timecard:view'
            );
        }

        $response = $this->getHtml(
           array_merge($param, array(
               'object' => $object,
               'externalStopPointId' => $externalStopPointId,
               'customerId' => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
               'timetableOnly' => true
           ))
       );

        $cssVersion = $object->getLineConfig()->getLayoutConfig()->getLayout()->getCssVersion();
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

    /**
     * Return url for object PDF file
     *
     * @param $object
     * @param $externalStopPointId
     * @return null|string
     * @throws \Exception
     */
    public function getStoppointPdfUrl($object, $externalStopPointId)
    {

        switch ($object) {
            case 'timetable':
                $url = $this->getUrlForTimetable($object, $externalStopPointId);
                break;
            case 'lineTimecard':
                $url = $this->getUrlForLineTimecard($object);
                break;
            default:
                throw new Exception('object ' . object . ' not supported');
        }

        return $url;
    }

    /**
     * Return url for LineTimecard  pdf file
     * @param LineTimecard $lineTimecard
     *
     * @return string $url
     */
    private function getUrlForLineTimecard(LineTimecard $lineTimecard)
    {
        $hash = $this->getPdfHash($lineTimecard, null);

        // Check if PDF is already generated
        if ($hash == $lineTimecard->getPdfHash()) {
            $pdfMedia = $this->mediaManager->getLineTimecardMedia($lineTimecard);
        } else {
            // Generate PDF
            $url = $this->getCurrentHttpHost() . $this->co->get('router')->generate(
                    'canal_tp_mtt_timecard_view',
                    array(
                        'externalNetworkId' => $lineTimecard->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                        'seasonId'          => $lineTimecard->getLineConfig()->getSeason()->getId(),
                        'externalLineId'    => $lineTimecard->getLineConfig()->getExternalLineId(),
                        'customerId'        => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
                        'timetableOnly'     => true,
                        'pdf'               => true
                    )
            );
            $layout = $lineTimecard->getLineConfig()->getLayoutConfig()->getLayout();
            $configuration = json_decode($layout->getConfiguration());
            $orientation = $layout->getOrientationAsString(
                $configuration->lineTpl->orientation
            );

            $pdfPath = $this->pdfGenerator->getPdf($url, $orientation);

            if ($pdfPath) {
                $pdfMedia = $this->mediaManager->savePdf($lineTimecard, $pdfPath);
                $this->lineTimecardRepo->updatePdfGenerationInfos($lineTimecard, $hash);
            } else {
                throw new \Exception('PdfGenerator Webservice returned an empty response.');
            }
        }

        return $this->mediaManager->getUrlByMedia($pdfMedia);
    }

    /**
     * Return url for Timetable pdf File
     * @param Timetable $timetable
     * @param $externalStopPointId
     * @return null|string
     * @throws \Exception
     */
    private function getUrlForTimetable(Timetable $timetable, $externalStopPointId)
    {
        $hash = $this->getPdfHash($timetable, $externalStopPointId);
        $stopPoint = $this->stopPoint->findOneByExternalId($externalStopPointId);

        if (!empty($stopPoint) && $hash == $stopPoint->getPdfHash()) {
            $pdfMedia = $this->mediaManager->getStopPointTimetableMedia($timetable, $externalStopPointId);
        } else {
            $url = $this->getCurrentHttpHost() . $this->co->get('router')->generate(
                    'canal_tp_mtt_timetable_view',
                    array(
                        'externalNetworkId' => $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                        'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                        'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                        'externalStopPointId'=> $externalStopPointId,
                        'externalRouteId'    => $timetable->getExternalRouteId(),
                        'customerId'        => $this->co->get('security.context')->getToken()->getUser()->getCustomer()->getId(),
                        'timetableOnly'     => true
                    )
                );

            $layout = $timetable->getLineConfig()->getLayoutConfig()->getLayout();
            $configuration = json_decode($layout->getConfiguration());
            $orientation = $layout->getOrientationAsString(
                $configuration->stopPointsTpl->orientation
            );

            $pdfPath = $this->pdfGenerator->getPdf($url,$orientation);

            if ($pdfPath) {
                $pdfMedia = $this->mediaManager->savePdf($timetable, $pdfPath, $externalStopPointId);
                $this->stopPoint->updatePdfGenerationInfos($externalStopPointId, $timetable, $hash);
            } else {
                throw new \Exception('PdfGenerator Webservice returned an empty response.');
            }
        }

        return $this->mediaManager->getUrlByMedia($pdfMedia);
    }
}
