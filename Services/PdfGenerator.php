<?php

/**
 * Symfony service to call the pdfGenerator webservice
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use fpdi;

class PdfGenerator
{
    private $serverUrl = null;
    private $layoutsConfig = null;

    public function __construct($server, $layoutsConfig)
    {
        $this->serverUrl = $server;
        $this->layoutsConfig = $layoutsConfig;
    }

    /*
     * @function calls the webservice http://hg.prod.canaltp.fr/ctp/pdfGenerator.git/summary
     */
    private function callWebservice($url)
    {
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // grab URL and pass it to the browser
        $pdfContent = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // close cURL resource, and free up system resources
        curl_close($ch);

        return $http_code == 200 && !empty($pdfContent) ? $pdfContent : false;
    }

    public function getPdf($url, $layout)
    {
        $params = array();
        $params['url'] = $url;
        if (isset($this->layoutsConfig[$layout])) {
            $params['orientation'] = $this->layoutsConfig[$layout]['orientation'];
        }
        // TODO: make these parameters configurable via layout config?
        $params['zoom'] = 2;
        $params['margin'] = 0;
        $generation_url = $this->serverUrl . '?' . http_build_query($params);

        $pdfContent = $this->callWebservice($generation_url);

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
    public function aggregatePdf($paths)
    {
        $fpdi = new \fpdi\FPDI();

        foreach ($paths as $file) {
            $pageCount = $fpdi->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                 $tplIdx = $fpdi->ImportPage($pageNo);
                 $s = $fpdi->getTemplatesize($tplIdx);
                 // Landscape/Portrait?
                 $fpdi->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
                 $fpdi->useTemplate($tplIdx);
            }
        }
        $dir = $this->getUploadRootDir() . '/';
        // TODO: should be generic and saved for later?
        $fpdi->Output($dir . 'concat.pdf', 'F');

        return '/uploads/concat.pdf';
    }

    protected function getUploadRootDir()
    {
        // TODO: should be configured
        // works if bundle is in vendor folder
        return realpath(__DIR__.'/../../../../../../web/uploads/');
    }
}
