<?php

namespace CanalTP\MttBundle\Tests\Unit\Calendar;

use CanalTP\MttBundle\Calendar\GridPeriodsCsv;
use CanalTP\MttBundle\CsvGenerator;

class GridPeriodsCsvTest extends \PHPUnit_Framework_TestCase
{
    use CalendarTrait;
    use CustomerTrait;

    public function getCsvModel()
    {
        $calendarA = $this->makeCalendar(1, 'calendarA1', '2016-01-01', '2016-06-01', '1111100');
        $calendarA->setCustomer($this->makeCustomer('cusA', ['networkA1', 'networkA2']));

        $calendarB = $this->makeCalendar(2, 'calendarB1', '2016-02-01', '2016-03-01', '0000011');
        $calendarB->setCustomer($this->makeCustomer('cusB', ['networkB1']));

        return [[new GridPeriodsCsv([$calendarA, $calendarB])]];
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGetHeaders($csvModel)
    {
        $this->assertEquals(['calendar_id', 'begin_date', 'end_date'], $csvModel->getHeaders());
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGetRows($csvModel)
    {
        $expected = [
            [1, '20160101', '20160601'],
            [2, '20160201', '20160301'],
        ];

        $this->assertEquals($expected, $csvModel->getRows());
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGetFilename($csvModel)
    {
        $this->assertEquals('grid_periods.txt', $csvModel->getFilename());
    }

    /**
     * @dataProvider getCsvModel
     */
    public function testGenerateCsvContent($csvModel)
    {
        $expected = <<<EOL
calendar_id,begin_date,end_date
1,20160101,20160601
2,20160201,20160301

EOL;

        $this->assertSame(str_replace("\r", '', $expected), CsvGenerator::generateCSV($csvModel));
    }
}
