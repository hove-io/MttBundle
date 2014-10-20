<?php

namespace CanalTP\MttBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\LineConfig;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Entity\Layout;

class Fixture extends AbstractFixture implements OrderedFixtureInterface
{
    const EXTERNAL_COVERAGE_ID = 'fr-cen';
    const EXTERNAL_NETWORK_ID = 'network:Filbleu';
    const TOKEN = '46cadd8a-e385-4169-9cb8-c05766eeeecb';
    const EXTERNAL_LINE_ID = 'line:TTR:Nav62';
    const EXTERNAL_ROUTE_ID = 'route:TTR:Nav155';
    const EXTERNAL_STOP_POINT_ID = 'stop_point:TTR:SP:STPGB-2';
    const SEASON_ID = 1;
    const AREA_ID = 1;
    const EXTERNAL_LAYOUT_CONFIG_ID_1 = 1;
    const EXTERNAL_LAYOUT_CONFIG_ID_2 = 2;
    public static $timetableId;

    public function createNetwork(
        ObjectManager $em,
        $externalNetworkId = Fixture::EXTERNAL_NETWORK_ID,
        $externalCoverageId = Fixture::EXTERNAL_COVERAGE_ID,
        $token = Fixture::TOKEN
    )
    {
        $network = $em->getRepository('CanalTPMttBundle:Network')->findOneByExternalId($externalNetworkId);
        if ($network != NULL) {
            return ($network);
        }
        $network = new Network();
        $network->setExternalId($externalNetworkId);
        $network->setExternalCoverageId($externalCoverageId);
        $network->setToken($token);

        $em->persist($network);

        return ($network);
    }

    private function createSeason(ObjectManager $em, $network)
    {
        $season = new Season();
        $season->setNetwork($network);
        $season->setTitle('hiver 2014');
        $season->setStartDate(new \DateTime("-1 year"));
        $season->setEndDate(new \DateTime("-6 month"));
        $season->setPublished(TRUE);

        $em->persist($season);

        return ($season);
    }

    private function createLineConfig(ObjectManager $em, $season, $layoutConfig)
    {
        $lineConfig = new LineConfig();
        $lineConfig->setSeason($season);
        $lineConfig->setLayoutConfig($layoutConfig);
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

        self::$timetableId = $timetable->getId();

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
        $network = $em->getRepository('CanalTPMttBundle:Network')->findOneByExternalId(Fixture::EXTERNAL_NETWORK_ID);
        $season = $this->createSeason($em, $network);
        $layoutConfig = $em->getRepository('CanalTPMttBundle:LayoutConfig')->find(Fixture::EXTERNAL_LAYOUT_CONFIG_ID_1);
        $lineConfig = $this->createLineConfig($em, $season, $layoutConfig);
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
