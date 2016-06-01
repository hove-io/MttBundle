<?php

namespace CanalTP\MttBundle\Tests\Twig;

use CanalTP\MttBundle\Twig\CalendarExtension;
use Prophecy\Argument;

class CalendarExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CanalTP\MttBundle\Twig\CalendarExtension
     */
    private $extension;

    protected function setUp()
    {
        parent::setUp();
        $translatorProphecy = $this->getTranslatorProphecy();
        $this->extension = new CalendarExtension($translatorProphecy->reveal());
    }

    /**
     * @dataProvider getCases
     */
    public function testCalendarRange($layout, $expected)
    {
        $result = $this->extension->calendarRange($layout);
        $this->assertEquals($result, $expected);
    }

    public function getCases()
    {
        $obj1 = $this->getMockBuilder('CanalTP\MttBundle\Entity\Layout')
            ->setMethods(
                array(
                    'getCalendarStart',
                    'getCalendarEnd'
                )
            )
            ->getMock();
        $obj2 = clone $obj1;
        $obj3 = clone $obj1;

        $obj1->expects($this->any())
            ->method('getCalendarStart')
            ->will($this->returnValue(4));
        $obj1->expects($this->any())
            ->method('getCalendarEnd')
            ->will($this->returnValue(1));

        $obj2->expects($this->any())
            ->method('getCalendarStart')
            ->will($this->returnValue(4));
        $obj2->expects($this->any())
            ->method('getCalendarEnd')
            ->will($this->returnValue(22));

        $obj3->expects($this->any())
            ->method('getCalendarStart')
            ->will($this->returnValue(10));
        $obj3->expects($this->any())
            ->method('getCalendarEnd')
            ->will($this->returnValue(6));

        return array(
            array(
                $obj1,
                array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,0,1)
            ),
            array(
                $obj2,
                array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22)
            ),
            array(
                $obj3,
                array(10,11,12,13,14,15,16,17,18,19,20,21,22,23,0,1,2,3,4,5,6)
            )
        );
    }

    /**
     * @dataProvider getWeeklyPatterns
     */
    public function testToWeeklyPattern($pattern, $expected)
    {
        $result = $this->extension->toWeekDays($pattern);
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider for testToWeeklyPattern method
     * @return array
     */
    public function getWeeklyPatterns()
    {
        return [
          ['1',       'Lundi'],
          ['010',     'Mardi'],
          ['011',     'Mardi, Mercredi'],
          ['0000001', 'Dimanche'],
          ['0000000', ''],
        ];
    }


    /**
     * Get Translator
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getTranslatorProphecy()
    {
        $weekDays = [
          'monday'    => 'Lundi',
          'tuesday'   => 'Mardi',
          'wednesday' => 'Mercredi',
          'thursday'  => 'Jeudi',
          'friday'    => 'Vendredi',
          'saturday'  => 'Samedi',
          'sunday'    => 'Dimanche',
        ];

        $translatorProphecy = $this->prophesize('\Symfony\Component\Translation\Translator');
        foreach ($weekDays as $key => $day) {
            $translatorProphecy->trans("calendar.weekdays.$key", [], "default")->willReturn($day);
        }

        return $translatorProphecy;
    }
}
