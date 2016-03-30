<?php

namespace CanalTP\MttBundle\Tests\Unit\Services;

use CanalTP\NmmPortalBundle\Entity\Perimeter;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Services\CalendarManager;

class CalendarManagerTest extends \PHPUnit_Framework_TestCase
{
    const COVERAGE = 'jdr';
    const CALENDAR_ID = 'XYZ';

    public function setUp()
    {
        $perimeterEntity = new Perimeter();
        $perimeterEntity->setExternalCoverageId(self::COVERAGE);
        $this->seasonEntity = new Season();
        $this->seasonEntity->setPerimeter($perimeterEntity);
    }

    public function testIsIncludedWithValidCalendar()
    {
        $this->seasonEntity->setStartDate(new \DateTime('2016-04-24'));
        $this->seasonEntity->setEndDate(new \DateTime('2016-05-01'));

        $navitiaServiceProphecy = $this->getNavitiaServiceProphecy();
        $translatorProphecy = $this->getTranslatorProphecy();

        $calendarManager = new CalendarManager($navitiaServiceProphecy->reveal(), $translatorProphecy->reveal());

        $this->assertTrue($calendarManager->isIncluded(self::CALENDAR_ID, $this->seasonEntity));
    }

    public function testIsIncludedWithCalendarOutOfSeason()
    {
        $this->seasonEntity->setStartDate(new \DateTime('2015-04-24'));
        $this->seasonEntity->setEndDate(new \DateTime('2015-05-01'));

        $navitiaServiceProphecy = $this->getNavitiaServiceProphecy();
        $translatorProphecy = $this->getTranslatorProphecy();

        $calendarManager = new CalendarManager($navitiaServiceProphecy->reveal(), $translatorProphecy->reveal());

        $this->assertFalse($calendarManager->isIncluded(self::CALENDAR_ID, $this->seasonEntity));
    }

    public function testIsIncludedWithUnknownCalendar()
    {
        $this->seasonEntity->setStartDate(new \DateTime('2016-04-24'));
        $this->seasonEntity->setEndDate(new \DateTime('2016-05-01'));

        $navitiaServiceProphecy = $this->prophesize('\CanalTP\MttBundle\Services\Navitia');
        $navitiaServiceProphecy
            ->getCalendar(self::COVERAGE, self::CALENDAR_ID)
            ->willThrow('\Navitia\Component\Exception\NotFound\UnknownObjectException');

        $translatorProphecy = $this->getTranslatorProphecy();

        $calendarManager = new CalendarManager($navitiaServiceProphecy->reveal(), $translatorProphecy->reveal());
        $this->assertFalse($calendarManager->isIncluded(self::CALENDAR_ID, $this->seasonEntity));
    }

    /**
     * Get Navitia Service
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getNavitiaServiceProphecy()
    {
        $calendarJson = <<<JSON
{
    "calendars": [
        {
            "active_periods": [
                {
                    "begin": "20160425",
                    "end": "20160430"
                }
            ]
        }
    ]
}
JSON;

        $navitiaServiceProphecy = $this->prophesize('\CanalTP\MttBundle\Services\Navitia');
        $navitiaServiceProphecy
            ->getCalendar(self::COVERAGE, self::CALENDAR_ID)
            ->willReturn(json_decode($calendarJson));

        return $navitiaServiceProphecy;
    }

    /**
     * Get Translator
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getTranslatorProphecy()
    {
        return $this->prophesize('\Symfony\Component\Translation\TranslatorInterface');
    }
}
