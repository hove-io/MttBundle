<?php

namespace CanalTP\MttBundle\Tests\Unit\Calendar;

use CanalTP\MttBundle\Calendar\CalendarExport;
use Prophecy\Argument;

class CalendarExportTest extends \PHPUnit_Framework_TestCase
{
    use CalendarTrait;
    use CustomerTrait;

    public function calendarExportProvider()
    {
        $calendarA = $this->makeCalendar(1, 'calendarA1', '2016-01-01', '2016-06-01', '1111100');
        $calendarA->setCustomer($this->makeCustomer('cusA', ['networkA1', 'networkA2']));

        $calendarB = $this->makeCalendar(2, 'calendarB1', '2016-02-01', '2016-03-01', '0000011');
        $calendarB->setCustomer($this->makeCustomer('cusB', ['networkB1']));

        return [
            [
                new CalendarExport(
                    $this->getHttpClientProphecy()->reveal(),
                    sys_get_temp_dir().'/calendars'
                ),
                [$calendarA, $calendarB]
            ],
        ];
    }

    /**
     * @return ObjectProphecy
     */
    private function getHttpClientProphecy()
    {
        $messageProphecy = $this->prophesize('\Guzzle\Http\Message\Response');

        $request = $this->prophesize('\Guzzle\Http\Message\EntityEnclosingRequestInterface');
        $request->addPostFile(Argument::type('string'), Argument::type('string'))->shouldBeCalled();

        $httpClientProphecy = $this->prophesize('\Guzzle\Http\Client');
        $httpClientProphecy->getBaseUrl()->shouldBeCalled();
        $httpClientProphecy
            ->post(
                Argument::type('array'),
                Argument::type('array'),
                null,
                Argument::type('array')
            )
            ->shouldBeCalled()
            ->willReturn($request->reveal());

        $httpClientProphecy
            ->send($request->reveal())
            ->shouldBeCalled()
            ->willReturn($messageProphecy->reveal());

        return $httpClientProphecy;
    }

    /**
     * @dataProvider calendarExportProvider
     */
    public function testExport(CalendarExport $calendarExport, $calendars)
    {
        $response = $calendarExport->export('fr-cen', $calendars);

        $this->assertInstanceOf('\Guzzle\Http\Message\Response', $response);
    }

    public static function tearDownAfterClass()
    {
        static::unlinKDir(sys_get_temp_dir().'/calendars');
    }

    private static function unlinKDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::unlinKDir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
