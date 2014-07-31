<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

use CanalTP\SamCoreBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Layout;


class FixturesMtt extends SamBaseFixture
{
    protected $em = null;

    const ROLE_USER_MTT  = 'ROLE_USER_MTT';
    const ROLE_ADMIN_MTT = 'ROLE_ADMIN_MTT';
    const ROLE_OBS_MTT = 'ROLE_OBS_MTT';

    protected $users = array(
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

    protected $roles = array(
        'role-user-mtt' => array(
            'BUSINESS_VIEW_NAVITIA_LOG',
            'BUSINESS_CHOOSE_LAYOUT',
            'BUSINESS_EDIT_LAYOUT',
            'BUSINESS_MANAGE_SEASON',
            'BUSINESS_MANAGE_DISTRIBUTION_LIST',
            'BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF',
            'BUSINESS_GENERATE_PDF'
        ),
        'role-admin-mtt' => array(
            'BUSINESS_VIEW_NAVITIA_LOG',
            'BUSINESS_CHOOSE_LAYOUT',
            'BUSINESS_ASSIGN_NETWORK_LAYOUT',
            'BUSINESS_EDIT_LAYOUT',
            'BUSINESS_MANAGE_SEASON',
            'BUSINESS_MANAGE_DISTRIBUTION_LIST',
            'BUSINESS_GENERATE_DISTRIBUTION_LIST_PDF',
            'BUSINESS_GENERATE_PDF'
        ),
        'role-obs-mtt' => array(),
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
        $token = '46cadd8a-e385-4169-9cb8-c05766eeeecb',
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
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network1, $network2)
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
            array($network1, $network2)
        );
        $this->createLayout(
            array(
                'label'         => 'Lianes 4 paves neutre',
                'twig'          => 'Divia/neutralLianes4Timegrids.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layouts/divia/neutral-Lianes-4-paves.png',
                'orientation'   => 'landscape',
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network5)
        );
        $this->createLayout(
            array(
                'label'         => 'Lianes 4 paves',
                'twig'          => 'Divia/lianes4Timegrids.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layouts/divia/Lianes-4-paves.png',
                'orientation'   => 'landscape',
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network5)
        );
        $this->createLayout(
            array(
                'label'         => 'Flexo',
                'twig'          => 'Divia/flexo.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layouts/divia/Flexo.png',
                'orientation'   => 'landscape',
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network5)
        );
        $this->createLayout(
            array(
                'label'         => 'Proxi',
                'twig'          => 'Divia/proxi.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layouts/divia/Proxi.png',
                'orientation'   => 'landscape',
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network5)
        );

        $this->em->persist($network1);
        $this->em->persist($network2);
        $this->em->flush();
    }

    public function load(ObjectManager $em)
    {
        $this->em = $em;
        $app = $this->createApplication('Mtt', '/mtt');

        $userRole    = $this->createApplicationRole('User Mtt',  self::ROLE_USER_MTT,  $app, $this->roles['role-user-mtt']);
        $this->addReference('role-user-mtt', $userRole);
        $addminRole  = $this->createApplicationRole('Admin Mtt', self::ROLE_ADMIN_MTT, $app, $this->roles['role-admin-mtt']);
        $this->addReference('role-admin-mtt', $addminRole);
        $obsRole  = $this->createApplicationRole('Observateur Mtt', self::ROLE_OBS_MTT, $app, $this->roles['role-obs-mtt']);
        $this->addReference('role-obs-mtt', $obsRole);
        $network1 = $this->createNetwork('network:Filbleu', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network2 = $this->createNetwork('network:Agglobus', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network3 = $this->createNetwork('network:SNCF', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network4 = $this->createNetwork('network:RATP', '46cadd8a-e385-4169-9cb8-c05766eeeecb');
        $network5 = $this->createNetwork('network:CGD', '46cadd8a-e385-4169-9cb8-c05766eeeecb', 'bourgogne');

        //associer les utilisateurs avec l'application
        foreach ($this->users as &$userData) {
            $userEntity = $this->createUser(
                $userData
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

    /**
     * @override
     */
    protected function createUser($data, array $roles = array())
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setFirstName($data['firstname']);
        $user->setLastName($data['lastname']);
        $user->setEnabled(true);
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        foreach ($data['roles'] as $roleRef) {
            $user->addUserRole($this->getReference($roleRef));
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
