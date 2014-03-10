<?php

namespace CanalTP\MttBundle\Tests\Twig;

use CanalTP\MttBundle\Twig\CalendarExtension;

class MediaFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        
    }
    
    /**
     * @dataProvider getCases
     */
    public function testCalendarRange($config, $expected)
    {
        $extension = new CalendarExtension();
        $result = $extension->calendarRange($config);
        $this->assertEquals($result, $expected);
    }
    
    public function getCases()
    {
        return array(
            array(
                array('start' => 4, 'end' => 1),
                array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,0,1)
            ),
            array(
                array('start' => 4, 'end' => 22),
                array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22)
            ),
            array(
                array('start' => 10, 'end' => 6),
                array(10,11,12,13,14,15,16,17,18,19,20,21,22,23,0,1,2,3,4,5,6)
            )
        );
    }
}