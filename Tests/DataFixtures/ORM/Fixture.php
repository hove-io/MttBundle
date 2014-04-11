<?php

namespace CanalTP\MttBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\LineConfig;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Entity\Layout;

class Fixture extends AbstractFixture implements OrderedFixtureInterface
{
    const EXTERNAL_COVERAGE_ID = 'Centre';
    const EXTERNAL_NETWORK_ID = 'network:Filbleu';
    const EXTERNAL_LINE_ID = 'line:TTR:Nav62';
    const EXTERNAL_ROUTE_ID = 'route:TTR:Nav168';
    const EXTERNAL_STOP_POINT_ID = 'stop_point:TTR:SP:STPGB-2';
    const SEASON_ID = 1;
    
    
    private function createUser(ObjectManager $em, $data)
    {
        //  On crée l'utilisateur admin 
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

    public function createNetwork(ObjectManager $em, $externalNetworkId = Fixture::EXTERNAL_NETWORK_ID, $externalCoverageId = Fixture::EXTERNAL_COVERAGE_ID)
    {
        $network = new Network();
        $network->setExternalId($externalNetworkId);
        $network->setExternalCoverageId($externalCoverageId);

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

    private function createLineConfig(ObjectManager $em, $season, $layout)
    {
        $lineConfig = new LineConfig();
        $lineConfig->setSeason($season);
        $lineConfig->setLayout($layout);
        $lineConfig->setExternalLineId(Fixture::EXTERNAL_LINE_ID);

        $em->persist($lineConfig);

        return ($lineConfig);
    }

    private function createTimetable(ObjectManager $em, $lineConfig)
    {
        $timetable = new Timetable();
        $timetable->setLineConfig($lineConfig);
        $timetable->setExternalRouteId(Fixture::EXTERNAL_ROUTE_ID);

        $em->persist($timetable);

        return ($timetable);
    }

    private function createBlock(ObjectManager $em, $timetable, $typeId = BlockRepository::TEXT_TYPE)
    {
        $block = new Block();
        $block->setTimetable($timetable);
        $block->setTypeId($typeId);
        $block->setDomId('timegrid_block_1');
        $block->setContent('test');
        $block->setTitle('title');

        $em->persist($block);

        return ($block);
    }

    private function createLayout(ObjectManager $em, $layoutProperties, $networks = array())
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
            $em->persist($network);
        }
        
        $em->persist($layout);
        return ($layout);
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
        $layout = $this->createLayout(
            $em,
            array(
                'label'         => 'Layout 1 de type paysage (Dijon 1)',
                'twig'          => 'layout_1.html.twig',
                'preview'       => '/bundles/canaltpmtt/img/layout_1.png',
                'orientation'   => 'landscape',
                'calendarStart' => 4,
                'calendarEnd'   => 1,
            ),
            array($network)
        );
        $lineConfig = $this->createLineConfig($em, $season, $layout);
        $timetable = $this->createTimetable($em, $lineConfig);
        $block = $this->createBlock($em, $timetable);
        
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
