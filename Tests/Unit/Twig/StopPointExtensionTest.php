<?php

namespace CanalTP\MttBundle\Tests\Twig;

use CanalTP\MttBundle\Twig\StopPointExtension;

class StopPointExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $codes = array();

    public function setUp()
    {
        $jsonCodes = <<<JSON
[
    {
        "type": "toto",
        "value": "1234"
    },
    {
        "type": "totem",
       "value": "654895"
    },
    {
        "type": "external_code",
        "value": "AFG2568"
    }
]
JSON;
        $this->codes = json_decode($jsonCodes);
    }

    /**
     * @dataProvider typeProvider
     */
    public function testGetCode($type, $expected)
    {
        $extension = new StopPointExtension();
        $this->assertEquals($expected, $extension->getCode($this->codes, $type));
    }

    public function typeProvider()
    {
        return array(
            array(
                null,
                null,
            ),
            array(
                'nonexistingtype',
                null,
            ),
            array(
                'totem',
                '654895',
            ),
            array(
                'external_code',
                '2568',
            ),
        );
    }
}
