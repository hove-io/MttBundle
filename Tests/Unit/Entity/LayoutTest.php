<?php

namespace CanalTP\MttBundle\Tests\Unit\Entity;

use CanalTP\MttBundle\Entity\Layout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getLayouts
     */
    public function testOrientationAsString($layout, $expected)
    {
        $this->assertEquals($expected, $layout->getOrientationAsString());
    }

    public function getLayouts()
    {
        $defaultLayout = new Layout();

        $landscapeLayout = new Layout();
        $landscapeLayout->setOrientation(LAYOUT::ORIENTATION_LANDSCAPE);

        $portraitLayout = new Layout();
        $portraitLayout->setOrientation(LAYOUT::ORIENTATION_PORTRAIT);

        $nonExistingOrientationLayout = new Layout();
        $nonExistingOrientationLayout->setOrientation('whatever');

        return array(
            array(
                $defaultLayout,
                'landscape'
            ),
            array(
                $landscapeLayout,
                'landscape'
            ),
            array(
                $portraitLayout,
                'portrait'
            ),
            array(
                $nonExistingOrientationLayout,
                'landscape'
            ),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid paperSize, "Invalid" given. Available paperSize are A3, A4, A5
     */
    public function testInvalidPaperSizeShouldThrowException()
    {
        $layout = new Layout();

        $layout->setPaperSize('Invalid');
    }

    /**
     * @dataProvider getPaperSizes
     */
    public function testSetValidPaperSize($actual, $expected)
    {
        $layout = new Layout();
        $layout->setPaperSize($actual);

        $this->assertEquals($expected, $layout->getPaperSize());
    }

    public function getPaperSizes()
    {
        return [
            ['a3', 'A3'],
            ['A3', 'A3'],
            ['a4', 'A4'],
            ['A4', 'A4'],
            ['a5', 'A5'],
            ['A5', 'A5'],
        ];
    }
}
