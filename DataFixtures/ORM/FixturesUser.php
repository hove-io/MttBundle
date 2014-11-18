<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamCoreBundle\DataFixtures\ORM\UserTrait;

class FixturesUser extends AbstractFixture implements OrderedFixtureInterface
{
    use UserTrait;

    private $users = array(
        array(
            'id'        => null,
            'username'  => 'mtt',
            'firstname' => 'mtt_firstname',
            'lastname'  => 'mtt_lastname',
            'email'     => 'mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-admin-mtt', 'role-user-mtt'),
            'customer'  => 'customer-canaltp'
        ),
        array(
            'id'        => null,
            'username'  => 'observateur TT',
            'firstname' => 'observateur',
            'lastname'  => 'TT',
            'email'     => 'obs-mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-obs-mtt'),
            'customer'  => 'customer-canaltp'
        ),
        array(
            'id'        => null,
            'username'  => 'utilisateur TT',
            'firstname' => 'utilisateur',
            'lastname'  => 'TT',
            'email'     => 'user-mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-user-mtt'),
            'customer'  => 'customer-canaltp'
        ),
        array(
            'id'        => null,
            'username'  => 'adminCTP TT',
            'firstname' => 'adminCTP',
            'lastname'  => 'TT',
            'email'     => 'admin-mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-admin-mtt'),
            'customer'  => 'customer-canaltp'
        )
    );

    public function load(ObjectManager $om)
    {
        foreach ($this->users as $userData) {
            $userEntity = $this->createUser($om, $userData);
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 5;
    }
}
