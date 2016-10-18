<?php

namespace CanalTP\MttBundle;

use CanalTP\SamEcoreApplicationManagerBundle\SamApplication;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CanalTPMttBundle extends Bundle implements SamApplication
{
    public function getCanonicalName()
    {
        return 'mtt';
    }
}
