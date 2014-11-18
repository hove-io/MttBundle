<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

use CanalTP\SamCoreBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\SamCoreBundle\DataFixtures\ORM\RoleTrait;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\LayoutConfig;
use CanalTP\MttBundle\Entity\Layout;

class FixturesRole extends SamBaseFixture
{
    use RoleTrait;

    const ROLE_USER_MTT  = 'ROLE_USER_MTT';
    const ROLE_ADMIN_MTT = 'ROLE_ADMIN_MTT';
    const ROLE_OBS_MTT = 'ROLE_OBS_MTT';

    private $permissions = array(
        'user-mtt' => array(
            'BUSINESS_VIEW_NAVITIA_LOG',
            'BUSINESS_CHOOSE_LAYOUT',
            'BUSINESS_EDIT_LAYOUT',
            'BUSINESS_MANAGE_SEASON',
            'BUSINESS_MANAGE_DISTRIBUTION_LIST',
            'BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF',
            'BUSINESS_GENERATE_PDF',
            'BUSINESS_LIST_AREA',
            'BUSINESS_MANAGE_AREA',
            'BUSINESS_LIST_LAYOUT_CONFIG',
            'BUSINESS_MANAGE_LAYOUT_CONFIG'
        ),
        'admin-mtt' => array(
            'BUSINESS_VIEW_NAVITIA_LOG',
            'BUSINESS_CHOOSE_LAYOUT',
            'BUSINESS_ASSIGN_NETWORK_LAYOUT',
            'BUSINESS_EDIT_LAYOUT',
            'BUSINESS_MANAGE_SEASON',
            'BUSINESS_MANAGE_DISTRIBUTION_LIST',
            'BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF',
            'BUSINESS_GENERATE_PDF',
            'BUSINESS_LIST_AREA',
            'BUSINESS_MANAGE_AREA',
            'BUSINESS_LIST_LAYOUT_CONFIG',
            'BUSINESS_MANAGE_LAYOUT_CONFIG',
            'BUSINESS_MANAGE_CUSTOMER'
        ),
        'obs-mtt' => array(),
    );

    public function load(ObjectManager $om)
    {
        $this->createApplicationRole($om, 'User Mtt',  self::ROLE_USER_MTT,  'app-timetable', 'user-mtt');
        $this->createApplicationRole($om, 'Admin Mtt',  self::ROLE_USER_MTT,  'app-timetable', 'admin-mtt');
        $this->createApplicationRole($om, 'Observateur Mtt',  self::ROLE_USER_MTT,  'app-timetable', 'obs-mtt');
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2;
    }
}
 