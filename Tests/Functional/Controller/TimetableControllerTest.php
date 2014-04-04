<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class TimetableControllerTest extends AbstractControllerTest
{
    private function getViewRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalLineId' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => Fixture::EXTERNAL_STOP_POINT_ID,
                'seasonId' => Fixture::SEASON_ID
            )
        );
    }
    
    public function testIndex()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());
        $blockTitle = $this->client->getContainer()->get('translator')->trans('season.block.title', array(), 'default');
        $this->assertTrue($crawler->filter('html:contains("' . $blockTitle . '")')->count() == 1);
        $this->assertTrue($crawler->filter('div#left-menu')->count() == 1);
    }

    public function testAnonymousAccess()
    {
        $anonymous = static::createClient();

        $route = $anonymous->getContainer()->get('router')->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalLineId' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => Fixture::EXTERNAL_STOP_POINT_ID,
                'seasonId' => Fixture::SEASON_ID
            ));
        $crawler = $anonymous->request('GET', $route);
        $this->assertEquals(
            200,
            $anonymous->getResponse()->getStatusCode(),
            'Response status NOK:' . $anonymous->getResponse()->getStatusCode()
        );
        $this->assertTrue($crawler->filter('div#left-menu')->count() == 0);
    }
}