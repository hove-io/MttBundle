<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class TimetableControllerTest extends AbstractControllerTest
{
    private function getViewRoute($seasonId, $externalStopPointId = 'stop_point:TTR:SP:STPGB-2')
    {
        return $this->generateRoute(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => 'network:Filbleu',
                'externalLineId' => 'line:TTR:Nav62',
                'externalRouteId' => 'route:TTR:Nav155',
                'externalStopPointId' => $externalStopPointId,
                "seasonId" => $seasonId
            )
        );
    }
    
    public function testSeasonBlockDates()
    {
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(1);
        // check on stopPoint page
        $crawler = $this->doRequestRoute($this->getViewRoute($season->getId()));
        $this->checkBlockAndDates($crawler, $season);
        // check on route page
        $crawler = $this->doRequestRoute($this->getViewRoute($season->getId(), false));
        $this->checkBlockAndDates($crawler, $season);
    }
    
    private function checkBlockAndDates($crawler, $season)
    {
        // Title present
        $blockTitle = $this->client->getContainer()->get('translator')->trans('season.block.title', array(), 'default');
        $this->assertTrue($crawler->filter('html:contains("' . $blockTitle . '")')->count() == 1);
        // TODO: Retrieve locale client.... returns null: $this->client->getContainer()->get('session')->get('_locale');
        $fmt = datefmt_create('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        // dates compliant
        $this->assertTrue($crawler->filter('html:contains("' . datefmt_format($fmt, $season->getStartDate()) . '")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("' . datefmt_format($fmt, $season->getEndDate()) . '")')->count() == 1);
    }
    
}