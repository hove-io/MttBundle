<?php

namespace CanalTP\MttBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\LineConfig;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Entity\Layout;
use CanalTP\MttBundle\Entity\LayoutCustomer;

class Fixture extends AbstractFixture implements OrderedFixtureInterface
{
    const EXTERNAL_COVERAGE_ID = 'jdr';
    const EXTERNAL_NETWORK_ID = 'network:JDR:1';
    const TOKEN = 'd5b0148c-36f4-443c-9818-1f2f74a00be0';
    const EXTERNAL_LINE_ID = 'line:JDR:M1'; // Line 1 Château de Vincennes - La Défense
    const EXTERNAL_ROUTE_ID = 'route:JDR:M1'; // Route Château de Vincennes - La Défense
    const EXTERNAL_STOP_POINT_ID = 'stop_point:JDR:SP:CHVIN1'; // Stop Point Château de Vincennes
    const SEASON_ID = 1;
    const AREA_ID = 1;
    const EXTERNAL_LAYOUT_CONFIG_ID_1 = 1;
    const EXTERNAL_LAYOUT_CONFIG_ID_2 = 2;
    public static $timetableId;

    private function createSeason(ObjectManager $em, $perimeter)
    {
        $season = new Season();
        $season->setPerimeter($perimeter);
        $season->setTitle('hiver 2014');
        $season->setStartDate(\DateTime::createFromFormat('d/m/Y', '21/12/2013'));
        $season->setEndDate(\DateTime::createFromFormat('d/m/Y', '21/03/2014'));
        $season->setPublished(true);

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

    private function createLayout(ObjectManager $em, $layoutProperties, $perimeters = array())
    {
        $layout = new Layout();
        $layout->setLabel($layoutProperties['label']);
        $layout->setTwig($layoutProperties['twig']);
        $layout->setPreview($layoutProperties['preview']);
        $layout->setOrientation($layoutProperties['orientation']);
        $layout->setCalendarStart($layoutProperties['calendarStart']);
        $layout->setCalendarEnd($layoutProperties['calendarEnd']);
        $layout->setPerimeters($perimeters);
        foreach ($perimeters as $perimeter) {
            $perimeter->addLayout($layout);
            $em->persist($perimeter);
        }

        $em->persist($layout);

        return ($layout);
    }

    private function assignLayoutToCustomer(ObjectManager $om, $customer)
    {
        $layout = $om->getRepository('CanalTPMttBundle:Layout')->find(1);
        $layoutCustomer = new LayoutCustomer();

        $layoutCustomer->setCustomer($customer);
        $layoutCustomer->setLayout($layout);
        $om->persist($layoutCustomer);
    }

    public function load(ObjectManager $em)
    {
        $customer = $em->getRepository('CanalTPNmmPortalBundle:Customer')->findOneByNameCanonical('canaltp');
        $season = $this->createSeason($em, $customer->getPerimeters()->filter(function ($perimeter) {
            return ($perimeter->getExternalNetworkId() == self::EXTERNAL_NETWORK_ID);
        })->first());
        $layoutConfig = $em->getRepository('CanalTPMttBundle:LayoutConfig')->find(Fixture::EXTERNAL_LAYOUT_CONFIG_ID_1);

        $lineConfig = $this->createLineConfig($em, $season, $layoutConfig);
        $timetable = $this->createTimetable($em, $lineConfig);
        $block = $this->createBlock($em, $timetable);

        $this->assignLayoutToCustomer($em, $customer);
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
