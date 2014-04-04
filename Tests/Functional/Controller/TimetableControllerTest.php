<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimetableControllerTest extends AbstractControllerTest
{
    private function getViewRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => 'network:Filbleu',
                'externalLineId' => 'line:TTR:Nav62',
                'externalRouteId' => 'route:TTR:Nav168',
                'externalStopPointId' => 'stop_point:TTR:SP:STPGB-2',
                "seasonId" => 1
            )
        );
    }
    
    public function testIndex()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());
        $blockTitle = $this->client->getContainer()->get('translator')->trans('season.block.title', array(), 'default');
        $this->assertTrue($crawler->filter('html:contains("' . $blockTitle . '")')->count() == 1);
    }
}