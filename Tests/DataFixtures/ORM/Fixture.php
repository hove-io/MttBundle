<?php

namespace CanalTP\MttBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\LineConfig;


class Fixture extends AbstractFixture implements OrderedFixtureInterface
{
    const EXTERNAL_COVERAGE_ID = 'Centre';
    const EXTERNAL_NETWORK_ID = 'network:Filbleu';
    const EXTERNAL_LINE_ID = 'line:TTR:Nav62';
    
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
        $network->setExternalId(Fixture::EXTERNAL_NETWORK_ID);
        $network->setExternalCoverageId(Fixture::EXTERNAL_COVERAGE_ID);

        $em->persist($network);
        
        return ($network);
    }

    private function createSeason(ObjectManager $em, $network)
    {
        $season = new Season();
        $season->setNetwork($network);
        $season->setTitle('hiver 2015');
        $season->setStartDate(new \DateTime("now"));
        $season->setEndDate(new \DateTime("+6 month"));

        $em->persist($season);
        
        return ($season);
    }

    private function createLineConfig(ObjectManager $em, $season)
    {
        $lineConfig = new LineConfig();
        $lineConfig->setSeason($season);
        $lineConfig->setLayout('layout_1');
        $lineConfig->setExternalLineId(Fixture::EXTERNAL_LINE_ID);

        $em->persist($lineConfig);

        return ($lineConfig);
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
        $season = $this->createSeason($em, $network);
        $lineConfig = $this->createLineConfig($em, $season);
        
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
