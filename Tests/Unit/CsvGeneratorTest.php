<?php

namespace CanalTP\MttBundle\Tests\Unit;

use CanalTP\MttBundle\CsvGenerator;

class CsvGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateCsv()
    {
        $csvModelProphesize = $this->prophesize('\CanalTP\MttBundle\CsvModelInterface');
        $csvModelProphesize->getHeaders()->willReturn(['header1', 'headers2', 'header3']);
        $csvModelProphesize->getRows()->willReturn([['value1', 'value2', 'value3'], ['value4', 'value5', 'value6']]);
        $csvModelProphesize->getFilename()->willReturn('file.csv');

        $expected = <<<EOL
header1,headers2,header3
value1,value2,value3
value4,value5,value6

EOL;

        $this->assertEquals(CsvGenerator::generateCSV($csvModelProphesize->reveal()), $expected);
    }
}
