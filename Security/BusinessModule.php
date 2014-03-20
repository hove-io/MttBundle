<?php

namespace CanalTP\MttBundle\Security;

use Symfony\Component\DependencyInjection\Container;
use CanalTP\Sam\Ecore\ApplicationManagerBundle\Security\AbstractBusinessModule;

class BusinessModule extends AbstractBusinessModule
{
    public function __construct(Container $co)
    {
        $this->permissions = $co->getParameter('permissions');
    }

    public function getId() {
        return 1;
    }
    
    public function getName()
    {
        return 'test';
    }

    public function getPermissions()
    {
        return ($this->permissions);
    }
}
