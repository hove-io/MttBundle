<?php

namespace CanalTP\MttBundle\Twig;

class StopPointExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('externalCode', array($this, 'getExternalCode')),
        );
    }

    public function getExternalCode($codes)
    {
        $externalCode = null;

        foreach ($codes as $code) {
            if ($code->type == 'external_code') {
                $externalCode = substr($code->value, 3);
                break ;
            }
        }

        return ($externalCode);
    }

    public function getName()
    {
        return 'stop_point_extension';
    }
}
