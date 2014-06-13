<?php

/**
 * Symfony service to call the pdfGenerator webservice
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Services\DistributionListManager;

use fpdi;

class PdfGenerator
{
    private $serverUrl = null;
    private $distributionListManager = null;

    public function __construct(
        CurlProxy $curlProxy,
        $server,
        DistributionListManager $distributionListManager
    )
    {
        $this->curlProxy = $curlProxy;
        $this->serverUrl = $server;
        $this->distributionListManager = $distributionListManager;
    }

    public function getPdf($url, $orientation)
    {
        $params = array();
        $params['url'] = $url;
        $params['orientation'] = $orientation;
        // TODO: make these parameters configurable via layout?
        $params['zoom'] = '2';
        $params['margin'] = '0';
        $generation_url = $this->serverUrl . '?' . http_build_query($params);

        $pdfContent = $this->curlProxy->get($generation_url);
        // create File
        $dir = sys_get_temp_dir() . '/';
        $filename = md5($pdfContent) . '.pdf';
        $path = $dir . $filename;
        $fs = new Filesystem();
        $fs->dumpFile($path, $pdfContent);

        return $path;
    }

    /**
     *  @function aggregate pdf files
     *
     *  @param $paths array Array with absolute path to pdf files
     */
    public function aggregatePdf($paths, Timetable $timetable)
    {
        $fpdi = new \fpdi\FPDI();

        foreach ($paths as $file) {
            if (file_exists($file)) {
                $pageCount = $fpdi->setSourceFile($file);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                     $tplIdx = $fpdi->ImportPage($pageNo);
                     $s = $fpdi->getTemplatesize($tplIdx);
                     // Landscape/Portrait?
                     $fpdi->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
                     $fpdi->useTemplate($tplIdx);
                }
            }
        }
        $path = $this->distributionListManager->generateAbsoluteDistributionListPdfPath($timetable);
        $pathDir = dirname($path);

        if (!is_dir($pathDir)) {
            mkdir($pathDir, 0777, true);
        }
        $fpdi->Output($path, 'F');
        return $this->distributionListManager->generateRelativeDistributionListPdfPath($timetable);
    }
}
