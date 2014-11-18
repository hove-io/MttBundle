<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\SamCoreBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\SamCoreBundle\DataFixtures\ORM\UserTrait;

class FixturesUser extends SamBaseFixture
{
    use UserTrait;

    private $users = array(
        'mtt' => array(
            'id'        => null,
            'username'  => 'mtt',
            'firstname' => 'mtt_firstname',
            'lastname'  => 'mtt_lastname',
            'email'     => 'mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-admin-mtt', 'role-user-mtt')
        ),
        array(
            'id'        => null,
            'username'  => 'observateur TT',
            'firstname' => 'observateur',
            'lastname'  => 'TT',
            'email'     => 'obs-mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-obs-mtt')
        ),
        array(
            'id'        => null,
            'username'  => 'utilisateur TT',
            'firstname' => 'utilisateur',
            'lastname'  => 'TT',
            'email'     => 'user-mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-user-mtt')
        ),
        array(
            'id'        => null,
            'username'  => 'adminCTP TT',
            'firstname' => 'adminCTP',
            'lastname'  => 'TT',
            'email'     => 'admin-mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-admin-mtt')
        )
    );

    public function load(ObjectManager $om)
    {
        foreach ($this->users as $userData) {
            $userEntity = $this->createUser($om, $userData);
        }
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 3;
    }
}
