<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Layout;

class FixtureMtt extends AbstractFixture implements OrderedFixtureInterface
{
    private $em = null;

    private function createUser($data)
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

        $this->em->persist($user);

        return ($user);
    }

    private function createLayout($layoutProperties, $networks = array())
    {
        $layout = new Layout();
        $layout->setLabel($layoutProperties['label']);
        $layout->setTwig($layoutProperties['twig']);
        $layout->setPreview($layoutProperties['preview']);
        $layout->setOrientation($layoutProperties['orientation']);
        $layout->setCalendarStart($layoutProperties['calendarStart']);
        $layout->setCalendarEnd($layoutProperties['calendarEnd']);
        $layout->setNetworks($networks);
        foreach ($networks as $network) {
            $network->addLayout($layout);
            $this->em->persist($network);
        }

        $this->em->persist($layout);

        return ($layout);
    }

    private function createNetwork($externalId = 'network:Filbleu', $externalCoverageId = 'Centre')
    {
        $network = new Network();
        $network->setExternalId($externalId);
        $network->setExternalCoverageId($externalCoverageId);

        $this->em->persist($network);

        return ($network);
    }

    public function load(ObjectManager $em)
    {
        $this->em = $em;
        $user = $this->createUser(
            array(
                'username' => 'mtt',
                'firstname' => 'mtt_firstname',
                'lastname' => 'mtt_lastname',
                'email' => 'mtt@canaltp.fr',
                'password' => 'mtt',
                'roles' => array('ROLE_ADMIN')
            )
        );

        $network = $this->createNetwork();
        $network->addUser($user);
        $network2 = $this->createNetwork('network:Agglobus');
        $network2->addUser($user);
        $network3 = $this->createNetwork('network:SNCF');
        $network3->addUser($user);
        $network4 = $this->createNetwork('network:RATP');
        $network4->addUser($user);

        $layout1 = $this->createLayout(
            array(
                'label'         => 'Layout 1 de type paysage (Dijon 1)',
                'twig'          => 'layout_1.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layout_1.png',
                'orientation'   => 'landscape',
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network, $network2)
        );
        $layout2 = $this->createLayout(
            array(
                'label'         => 'Layout 2 de type paysage (Dijon 2)',
                'twig'          => 'layout_2.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layout_2.png',
                'orientation'   => 'landscape',
                'calendarStart'=> 4,
                'calendarEnd'  => 1,
            ),
            array($network)
        );
        $this->em->persist($network);
        $this->em->persist($network2);
        $this->em->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 1;
    }
}
