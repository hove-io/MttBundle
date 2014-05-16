<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\SamBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Layout;


class FixturesMtt extends SamBaseFixture
{
    protected $em = null;

    const ROLE_USER_MTT  = 'ROLE_USER_MTT';
    const ROLE_ADMIN_MTT = 'ROLE_ADMIN_MTT';

    protected $users = array(
        'mtt' => array(
            'id'        => null,
            'username'  => 'mtt',
            'firstname' => 'mtt_firstname',
            'lastname'  => 'mtt_lastname',
            'email'     => 'mtt@canaltp.fr',
            'password'  => 'mtt',
            'roles'     => array('role-admin-mtt', 'role-user-mtt')
        )
    );

    protected $userPermissions = array(
        'BUSINESS_VIEW_NAVITIA_LOG',
        'BUSINESS_CHOOSE_LAYOUT',
        'BUSINESS_ASSIGN_NETWORK_LAYOUT',
        'BUSINESS_EDIT_LAYOUT',
        'BUSINESS_MANAGE_SEASON',
        'BUSINESS_MANAGE_DISTRIBUTION_LIST',
        'BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF',
        'BUSINESS_GENERATE_PDF'
    );

    protected $adminPermissions = array(
        'BUSINESS_VIEW_NAVITIA_LOG',
        'BUSINESS_CHOOSE_LAYOUT',
        'BUSINESS_ASSIGN_NETWORK_LAYOUT',
        'BUSINESS_EDIT_LAYOUT',
        'BUSINESS_MANAGE_SEASON',
        'BUSINESS_MANAGE_DISTRIBUTION_LIST',
        'BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF',
        'BUSINESS_GENERATE_PDF'
    );

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

    private function createNetwork(
        $externalId = 'network:Filbleu',
        $token = 'unknown',
        $externalCoverageId = 'centre'
    )
    {
        $network = new Network();
        $network->setExternalId($externalId);
        $network->setExternalCoverageId($externalCoverageId);
        $network->setToken($token);

        $this->em->persist($network);
        return ($network);
    }

    private function createLayouts($network1, $network2, $network5)
    {
        $layout1 = $this->createLayout(
            array(
                'label'         => 'Layout 1 de type paysage (Dijon 1)',
                'twig'          => 'layout_1.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layout_1.png',
                'orientation'   => 'landscape',
                'calendarStart' => 5,
                'calendarEnd'   => 0,
            ),
            array($network1, $network2, $network5)
        );
        $layout2 = $this->createLayout(
            array(
                'label'         => 'Layout 2 de type paysage (Dijon 2)',
                'twig'          => 'layout_2.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layout_2.png',
                'orientation'   => 'landscape',
                'calendarStart'=> 5,
                'calendarEnd'  => 0,
            ),
            array($network1, $network5)
        );

        $this->em->persist($network1);
        $this->em->persist($network2);
        $this->em->flush();
    }

    public function load(ObjectManager $em)
    {
        $this->em = $em;
        $app = $this->createApplication('mtt', '/mtt');
        $userRole    = $this->createApplicationRole('User Mtt',  self::ROLE_USER_MTT,  $app, $this->userPermissions);
        $addminRole  = $this->createApplicationRole('Admin Mtt', self::ROLE_ADMIN_MTT, $app, $this->adminPermissions);
        $network1 = $this->createNetwork('network:Filbleu', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network2 = $this->createNetwork('network:Agglobus', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network3 = $this->createNetwork('network:SNCF', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network4 = $this->createNetwork('network:RATP', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network5 = $this->createNetwork('network:CGD', '46cadd8a-e385-4169-9cb8-c05766eeeecb', 'bourgogne');

        //associer les utilisateurs avec l'application
        foreach ($this->users as &$userData) {
            $isAdmin = in_array(self::ROLE_ADMIN_MTT, $userData['roles']);

            $userEntity = $this->createUser(
                $userData,
                (($isAdmin) ? array($addminRole) : array($userRole))
            );
            $userData['id'] = $userEntity->getId();

            $network1->addUser($userEntity);
            $network2->addUser($userEntity);
            $network3->addUser($userEntity);
            $network4->addUser($userEntity);
            $network5->addUser($userEntity);
        }
        $this->createLayouts($network1, $network2, $network5);
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 3;
    }
}
