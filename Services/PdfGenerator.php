<?php

/**
 * Symfony service to call the pdfGenerator webservice
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

class PdfGenerator
{
    private $config = null;

    public function __construct(string $server)
    {
        var_dump($server);die;
        $this->config = $config;
    }
}
