<?php

namespace CanalTP\MttBundle\Api;

use Symfony\Component\DependencyInjection\Container;
use CanalTP\Sam\Ecore\ApplicationManagerBundle\Api\AbstractBusinessModule;

class BusinessModule extends AbstractBusinessModule
{
    public function __construct(Container $co)
    {
        $this->permissions = $co->getParameter('permissions');
    }

    public function getPermissions()
    {
        return ($this->permissions);
    }
}
