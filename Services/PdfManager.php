<?php

/**
 * Description of PdfManager
 *
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\StopPoint;
use CanalTP\MttBundle\Entity\Timetable;

class PdfManager
{
    private $router = null;
    private $stopPoint = null;
    private $pdfGenerator = null;
    private $mediaManager = null;
    private $co = null;
    private $curlProxy = null;

    public function __construct(
        ObjectManager $om, 
        Router $router, 
        PdfGenerator $pdfGenerator,
        MediaManager $mediaManager,
        Container $co,
        CurlProxy $curlProxy
    )
    {
        $this->stopPoint = $om->getRepository('CanalTPMttBundle:StopPoint');
        $this->router = $router;
        $this->pdfGenerator = $pdfGenerator;
        $this->mediaManager = $mediaManager;
        $this->co = $co;
        $this->curlProxy = $curlProxy;
    }

    private function getHtmlHash($layoutWrapper)
    {
        if ($layoutWrapper->count() == 1) {
            return md5($layoutWrapper->html());
        } else {
            throw new \Exception('PdfManager - Hash generation impossible - Found zero or more than one #layout-main-wrapper.');
        }
    }

    private function getImagesHash($layoutWrapper)
    {
        $images = $layoutWrapper->filter('img');
        if ($images->count() > 0) {
            $urls = $images->extract(array('src'));
            $toHash = '';
            foreach ($urls as $url) {
                if ($imageContent = $this->curlProxy->get($url)) {
                    $toHash .= $imageContent;
                }
            }
            return empty($toHash) ? '' : md5($toHash);
        } else {
            return '';
        }
    }
    
    public function getPdfHash(Timetable $timetable, $url)
    {
        $htmlContent = $this->curlProxy->get($url);
        $crawler = new Crawler($htmlContent);
        $layoutWrapper = $crawler->filter('div#layout-main-wrapper');
        $htmlHash = $this->getHtmlHash($layoutWrapper);
        $imagesHash = $this->getImagesHash($layoutWrapper);
        $cssVersion = $timetable->getLineConfig()->getLayout()->getCssVersion();
        
        return md5($htmlHash . $imagesHash . $cssVersion);
    }
    
    public function getStoppointPdfUrl(Timetable $timetable, $externalStopPointId)
    {
        $url = $this->co->get('request')->getHttpHost() . $this->router->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => $timetable->getLineConfig()->getSeason()->getNetwork()->getExternalId(),
                'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                'externalStopPointId'=> $externalStopPointId,
                'externalRouteId'    => $timetable->getExternalRouteId()
            )
        );
        $hash = $this->getPdfHash($timetable, $url);
        $stopPoint = $this->stopPoint->findOneByExternalId($externalStopPointId);

        if ($hash == $stopPoint->getPdfHash()) {
            $pdfMedia = $this->mediaManager->getStopPointTimetableMedia($timetable, $externalStopPointId);
        } else {
            $pdfPath = $this->pdfGenerator->getPdf($url, $timetable->getLineConfig()->getLayout());
            if ($pdfPath) {
                $pdfMedia = $this->mediaManager->saveFile($timetable, $externalStopPointId, $pdfPath);
                $this->stopPoint->updatePdfGenerationInfos($externalStopPointId, $timetable, $hash);
            } else {
                throw new \Exception('PdfGenerator Webservice returned an empty response.');
            }
        }
        return $this->mediaManager->getUrlByMedia($pdfMedia);
    }
}