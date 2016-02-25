<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\DataFixtures\ORM\RoleTrait;

class FixturesRole extends AbstractFixture implements OrderedFixtureInterface
{
    use RoleTrait;

    private $roles = array(
        array(
            'name'          => 'User MTT',
            'reference'     => 'user-mtt',
            'application'   => 'app-mtt',
            'isEditable'    => true,
            'permissions'   => array(
                'BUSINESS_VIEW_NAVITIA_LOG',
                'BUSINESS_CHOOSE_LAYOUT',
                'BUSINESS_EDIT_LAYOUT',
                'BUSINESS_MANAGE_SEASON',
                'BUSINESS_GENERATE_PDF',
                'BUSINESS_LIST_AREA',
                'BUSINESS_MANAGE_AREA',
                'BUSINESS_MANAGE_LAYOUT_CONFIG'
            )
        ),
        array(
            'name'          => 'Admin MTT',
            'reference'     => 'admin-mtt',
            'application'   => 'app-mtt',
            'isEditable'    => true,
            'permissions'  => array(
                'BUSINESS_VIEW_NAVITIA_LOG',
                'BUSINESS_CHOOSE_LAYOUT',
                'BUSINESS_ASSIGN_NETWORK_LAYOUT',
                'BUSINESS_EDIT_LAYOUT',
                'BUSINESS_MANAGE_SEASON',
                'BUSINESS_GENERATE_PDF',
                'BUSINESS_LIST_AREA',
                'BUSINESS_MANAGE_AREA',
                'BUSINESS_MANAGE_LAYOUT_MODEL',
                'BUSINESS_ASSIGN_MODEL',
                'BUSINESS_MANAGE_LAYOUT_CONFIG',
            )
        ),
        array(
            'name'          => 'Observator MTT',
            'reference'     => 'obs-mtt',
            'application'   => 'app-mtt',
            'isEditable'    => true,
            'permissions'  => array()
        )
    );

    public function load(ObjectManager $om)
    {
         foreach ($this->roles as $role) {
            $this->createApplicationRole($om,  $role);
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2;
    }
}
