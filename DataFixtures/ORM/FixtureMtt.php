<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

use CanalTP\SamCoreBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\LayoutConfig;
use CanalTP\MttBundle\Entity\Layout;

class FixtureMtt extends SamBaseFixture
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
			'customer'  => 'customer-ctp',
            'password'  => 'mtt',
            'roles'     => array('role-admin-mtt', 'role-user-mtt')
        ),
        array(
            'id'        => null,
            'username'  => 'observateur TT',
            'firstname' => 'observateur',
            'lastname'  => 'TT',
            'email'     => 'obs-mtt@canaltp.fr',
			'customer'  => 'customer-ctp',
            'password'  => 'mtt',
            'roles'     => array('role-obs-mtt')
        ),
        array(
            'id'        => null,
            'username'  => 'utilisateur TT',
            'firstname' => 'utilisateur',
            'lastname'  => 'TT',
            'email'     => 'user-mtt@canaltp.fr',
			'customer'  => 'customer-ctp',
            'password'  => 'mtt',
            'roles'     => array('role-user-mtt')
        ),
        array(
            'id'        => null,
            'username'  => 'adminCTP TT',
            'firstname' => 'adminCTP',
            'lastname'  => 'TT',
            'email'     => 'admin-mtt@canaltp.fr',
			'customer'  => 'customer-ctp',
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
            'BUSINESS_GENERATE_PDF',
            'BUSINESS_LIST_AREA',
            'BUSINESS_MANAGE_AREA',
            'BUSINESS_LIST_LAYOUT_CONFIG',
            'BUSINESS_MANAGE_LAYOUT_CONFIG'
        ),
        'role-admin-mtt' => array(
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
        'role-obs-mtt' => array(),
    );

    private function createLayout($layoutProperties, $networks = array())
    {
        $layout = new Layout();
        $layout->setLabel($layoutProperties['label']);
        $layout->setPath($layoutProperties['path']);
        $layout->setPreviewPath($layoutProperties['previewPath']);
        $layout->setOrientation($layoutProperties['orientation']);
        $layout->setNotesModes($layoutProperties['notesModes']);
        $layout->setCssVersion($layoutProperties['cssVersion']);

        $this->em->persist($layout);

        return ($layout);
    }

    private function createLayoutConfig($layoutConfigProperties, Layout $layout, $networks = array())
    {
        $layoutConfig = new LayoutConfig();
        $layoutConfig->setLabel($layoutConfigProperties['label']);
        $layoutConfig->setCalendarStart($layoutConfigProperties['calendarStart']);
        $layoutConfig->setCalendarEnd($layoutConfigProperties['calendarEnd']);
        $layoutConfig->setNotesMode($layoutConfigProperties['notesMode']);
        $layoutConfig->setLayout($layout);
        // $layoutConfig->setPerimeters($networks);

        $this->em->persist($layoutConfig);

        return ($layoutConfig);
    }

    private function createLayouts()
    {
        $this->createLayoutConfig(
            array(
                'label' => 'Template par défaut',
                'calendarStart' => 5,
                'calendarEnd' => 22,
                'notesMode' => 1
            ),
            $this->createLayout(
                array(
                    'label'         => 'Template par défaut',
                    'path'          => 'default.html.twig',
                    'previewPath'   => '/bundles/canaltpmtt/img/default.png',
                    'orientation'   => Layout::ORIENTATION_LANDSCAPE,
                    'notesModes'    => array(LayoutConfig::NOTES_MODE_DISPATCHED),
                    'cssVersion'    => 1
                )
            )
        );

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

        //associer les utilisateurs avec l'application
        foreach ($this->users as &$userData) {
            $userEntity = $this->createUser(
                $userData
            );
            $userData['id'] = $userEntity->getId();
        }
        $this->createLayouts();
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
		$user->setCustomer($this->getReference($data['customer']));

        foreach ($data['roles'] as $roleRef) {
            $user->addUserRole($this->getReference($roleRef));
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
