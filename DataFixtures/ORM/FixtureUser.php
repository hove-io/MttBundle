<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamCoreBundle\Entity\User;
use CanalTP\MttBundle\Entity\Network;


class FixturesUser extends AbstractFixture implements OrderedFixtureInterface
{
    private function createUser(ObjectManager $em, $data)
    {
        //  On crée l'utilisateur admin akambi
        $user = new User();
        $user->setUsername($data['username']);
        $user->setFirstName($data['firstname']);
        $user->setLastName($data['lastname']);
        $user->setEnabled(true);
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        $user->setRoles($data['roles']);

        $em->persist($user);

        return ($user);
    }

    private function createNetwork(ObjectManager $em)
    {
        $network = new Network();
        $network->setExternalId('network:Filbleu');
        $network->setExternalCoverageId('Centre');

        $em->persist($network);
        return ($network);
    }

    private function joinUserAndNetwork(ObjectManager $em)
    {
        $network = new UsersNetworks();
        $network->setExternalId('network:Filbleu');
        $network->setExternalCoverageId('Centre');

        $em->persist($network);
        return ($network);
    }


    public function load(ObjectManager $em)
    {
        $network = $this->createNetwork($em);
        $user = $this->createUser(
            $em,
            array(
                'username' => 'mtt',
                'firstname' => 'mtt_firstname',
                'lastname' => 'mtt_lastname',
                'email' => 'mtt@canaltp.fr',
                'password' => 'mtt',
                'roles' => array('ROLE_ADMIN')
            )
        );

        $network->addUser($user);

        $em->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 1;
    }
}
