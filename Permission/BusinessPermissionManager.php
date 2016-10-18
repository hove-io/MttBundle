<?php

namespace CanalTP\MttBundle\Permission;

use CanalTP\SamEcoreApplicationManagerBundle\Permission\AbstractBusinessPermissionManager;

/**
 * Description of BusinessComponent
 *
 * @author akambi
 * @author Kévin ZIEMIANSKI <kevin.ziemianski@canaltp.fr>
 */
class BusinessPermissionManager extends AbstractBusinessPermissionManager
{
    private $businessModule;

    public function __construct($businessModule)
    {
        $this->businessModule = $businessModule;
    }

    public function getBusinessModules()
    {
        return $this->businessModule;
    }
}
