<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class TimetableControllerTest extends AbstractControllerTest
{
    private function getViewRoute($seasonId = 1)
    {
        return $this->generateRoute(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => 'network:Filbleu',
                'externalLineId' => 'line:TTR:Nav62',
                'externalRouteId' => 'route:TTR:Nav155',
                'externalStopPointId' => 'stop_point:TTR:SP:STPGB-2',
                "seasonId" => $seasonId
            )
        );
    }
    
    public function testSeasonBlockDates()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());
        // Title present
        $blockTitle = $this->client->getContainer()->get('translator')->trans('season.block.title', array(), 'default');
        $this->assertTrue($crawler->filter('html:contains("' . $blockTitle . '")')->count() == 1);
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(1);
        $fmt = datefmt_create('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $this->assertTrue($crawler->filter('html:contains("' . datefmt_format($fmt, $season->getStartDate()) . '")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("' . datefmt_format($fmt, $season->getEndDate()) . '")')->count() == 1);
        
    }
}