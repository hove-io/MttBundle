<?php

namespace CanalTP\MttBundle\Tests\Unit\Calendar;

use CanalTP\MttBundle\Calendar\GridCalendarsCsv;
use CanalTP\MttBundle\CsvGenerator;

class GridCalendarsCsvTest extends \PHPUnit_Framework_TestCase
{
    use CalendarTrait;
    use CustomerTrait;

    public function getCsvModel()
    {
        $calendarA = $this->makeCalendar(1, 'calendarA1', '2016-01-01', '2016-06-01', '1111100');
        $calendarA->setCustomer($this->makeCustomer('cusA', ['networkA1', 'networkA2']));

        $calendarB = $this->makeCalendar(2, 'calendarB1', '2016-02-01', '2016-03-01', '0000011');
        $calendarB->setCustomer($this->makeCustomer('cusB', ['networkB1']));

        return [[new GridCalendarsCsv([$calendarA, $calendarB])]];
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGetHeaders($csvModel)
    {
        $expected = [
            'grid_calendar_id',
            'name',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
        $this->assertEquals($expected, $csvModel->getHeaders());
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGetRows($csvModel)
    {
        $expected = [
            [1, 'calendarA1', 1, 1, 1, 1, 1, 0, 0],
            [2, 'calendarB1', 0, 0, 0, 0, 0, 1, 1],
        ];

        $this->assertEquals($expected, $csvModel->getRows());
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGetFilename($csvModel)
    {
        $this->assertEquals('grid_calendars.txt', $csvModel->getFilename());
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGenerateCsvContent($csvModel)
    {
        $expected = <<<EOL
grid_calendar_id,name,monday,tuesday,wednesday,thursday,friday,saturday,sunday
1,calendarA1,1,1,1,1,1,0,0
2,calendarB1,0,0,0,0,0,1,1

EOL;

        $this->assertSame(str_replace("\r", '', $expected), CsvGenerator::generateCSV($csvModel));
    }
}
