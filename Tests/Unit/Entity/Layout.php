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

}
