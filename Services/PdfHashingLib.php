<?php

/**
 * Description of PdfHashingLib
 *
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\DomCrawler\Crawler;

class PdfHashingLib
{
    private $curlProxy = null;
    
    public function __construct(CurlProxy $curlProxy)
    {
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
    
    public function getPdfHash($html, $cssVersion)
    {
        $crawler = new Crawler($html);
        $layoutWrapper = $crawler->filter('div#layout-main-wrapper');
        $htmlHash = $this->getHtmlHash($layoutWrapper);
        $imagesHash = $this->getImagesHash($layoutWrapper);
        
        return md5($htmlHash . $imagesHash . $cssVersion);
    }
    
}