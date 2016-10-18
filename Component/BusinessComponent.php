<?php

namespace CanalTP\MttBundle\Component;

use CanalTP\MttBundle\Services\UserManager;
use CanalTP\MttBundle\Menu\BusinessMenuItem;
use CanalTP\SamEcoreApplicationManagerBundle\Component\AbstractBusinessComponent;
use CanalTP\SamEcoreApplicationManagerBundle\Perimeter\BusinessPerimeterManagerInterface;
use CanalTP\SamEcoreApplicationManagerBundle\Permission\BusinessPermissionManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\Container;
use CanalTP\MttBundle\Menu\MenuManager;

/**
 * Description of BusinessComponent
 *
 * @author akambi
 * @author David Quintanel <david.quintanel@canaltp.fr>
 */
class BusinessComponent extends AbstractBusinessComponent
{
    private $businessPermissionManager;
    private $businessPerimeterManager;
    private $menuManager;

    public function __construct(
        BusinessPermissionManagerInterface $businessPermissionManager,
        BusinessPerimeterManagerInterface $businessPerimeterManager,
        MenuManager $menuManager
    ) {
        $this->businessPermissionManager = $businessPermissionManager;
        $this->businessPerimeterManager = $businessPerimeterManager;
        $this->menuManager = $menuManager;
    }

    public function getId()
    {
        return 'mtt_business_component';
    }

    public function getName()
    {
        return 'Business component MTT';
    }

    public function hasPerimeters()
    {
        $perimeters = $this->getPerimetersManager()->getPerimeters();

        return !empty($perimeters);
    }

    public function getMenuItems()
    {
        return $this->menuManager->getMenu();
    }

    public function getPerimetersManager()
    {
        return $this->businessPerimeterManager;
    }

    public function getPermissionsManager()
    {
        return $this->businessPermissionManager;
    }
}
