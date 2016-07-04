<?php

namespace CanalTP\MttBundle\Tests\Unit\Calendar;

use CanalTP\MttBundle\Calendar\CalendarArchiveGenerator;
use ZipArchive;
use Prophecy\Argument;

class CalendarArchiveGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return CalendarArchiveGenerator[]
     */
    public function zipProvider()
    {
        if (!is_dir($folder = __DIR__.'/files')) {
            mkdir($folder, 0777);
        }
        if (is_file($location = $folder.'/'.uniqid().'.zip')) {
            unlink($location);
        }

        return [
            [new CalendarArchiveGenerator($location, new ZipArchive())],
        ];
    }

    /**
     * @dataProvider zipProvider
     */
    public function testGetArchive(CalendarArchiveGenerator $archiveGenerator)
    {
        $this->assertInstanceOf('ZipArchive', $archiveGenerator->getArchive());
    }

    /**
     * @expectedException  \LogicException
     */
    public function testZipOpenFails()
    {
        $zipArchiveProphecy = $this->prophesize('\ZipArchive');
        $zipArchiveProphecy->open(Argument::type('string'), ZipArchive::CREATE)->willReturn(false);
        $archiveGenerator = new CalendarArchiveGenerator('location', $zipArchiveProphecy->reveal());
    }

    /**
     * @dataProvider zipProvider
     */
    public function testGenerateArchive(CalendarArchiveGenerator $archiveGenerator)
    {
        $csvModelProphesize = $this->prophesize('\CanalTP\MttBundle\CsvModelInterface');
        $csvModelProphesize->getHeaders()->willReturn(['header1', 'headers2', 'header3']);
        $csvModelProphesize->getRows()->willReturn([['value1', 'value2', 'value3'], ['value4', 'value5', 'value6']]);
        $csvModelProphesize->getFilename()->willReturn('file.csv');

        $archiveGenerator->addCsv($csvModelProphesize->reveal());
        $archive = $archiveGenerator->getArchive();

        $this->assertEquals(1, $archive->numFiles);
        $this->assertEquals('file.csv', $archive->statIndex(0)['name']);

    }

    public static function tearDownAfterClass()
    {
        static::unlinKDir(__DIR__.'/files');
    }

    private static function unlinKDir($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::unlinKDir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
