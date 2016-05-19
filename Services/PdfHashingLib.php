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
            throw new \Exception('PdfHashingLib - Hash generation impossible - Found zero or more than one #layout-main-wrapper.');
        }
    }

    public function getPdfHash($html, $cssVersion)
    {
        $crawler = new Crawler($html);
        $layoutWrapper = $crawler->filter('div#layout-main-wrapper');
        $htmlHash = $this->getHtmlHash($layoutWrapper);

        return md5($htmlHash . $cssVersion);
    }
}
