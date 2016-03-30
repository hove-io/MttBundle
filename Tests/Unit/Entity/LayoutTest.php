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
     * @expectedExceptionMessage Invalid pageSize, "Invalid" given. Available pageSize are A3, A4, A5.
     */
    public function testInvalidPageSizeShouldThrowException()
    {
        $layout = new Layout();

        $layout->setPageSize('Invalid');
    }

    /**
     * @dataProvider getPageSizes
     */
    public function testSetValidPageSize($actual, $expected)
    {
        $layout = new Layout();
        $layout->setPageSize($actual);

        $this->assertEquals($expected, $layout->getPageSize());
    }

    public function getPageSizes()
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
