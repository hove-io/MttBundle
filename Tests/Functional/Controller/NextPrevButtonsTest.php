<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

// METH-167 test suite
class NextPrevButtonsTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $this->setService('canal_tp_mtt.navitia', $this->getMockedNavitia());
    }

    private function getRoute($stopPointId = 'stop_point:TTR:SP:JUSTB-1')
    {
        return $this->generateRoute(
            'canal_tp_mtt_calendar_view',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => $stopPointId,
                'currentSeasonId' => Fixture::SEASON_ID
            )
        );
    }

    public function testIsFirstStopOnlyOneButton()
    {
        $crawler = $this->doRequestRoute($this->getRoute());
        $btn_count = $crawler->filter('#main-container > div.row > div.col-md-8 > div.row a')->count();
        $this->assertTrue($btn_count == 1, "Expected one button for first stop. Found :$btn_count");
        $btn_count = $crawler->filter('#main-container > div.row > div.col-md-8 > div.row a span.glyphicon-chevron-right')->count();
        $this->assertTrue($btn_count == 1, "Expected one button with right chevron for first stop. Found :$btn_count");
    }

    public function testRandomStopTwoButtons()
    {
        $this->setService('canal_tp_mtt.navitia', $this->getMockedNavitia());
        $crawler = $this->doRequestRoute($this->getRoute('stop_point:TTR:SP:LENIB-1'));
        $btn_count = $crawler->filter('#main-container > div.row > div.col-md-8 > div.row a')->count();
        $this->assertTrue($btn_count == 2, "Expected two buttons for random stop. Found :$btn_count");
    }

    public function testIsLastStopOnlyOnePrevButton()
    {
        $this->setService('canal_tp_mtt.navitia', $this->getMockedNavitia());
        $crawler = $this->doRequestRoute($this->getRoute('stop_point:TTR:SP:PADOB-1'));
        $btn_count = $crawler->filter('#main-container > div.row > div.col-md-8 > div.row a')->count();
        $this->assertTrue($btn_count == 1, "Expected one button for first stop. Found :$btn_count");
        $btn_count = $crawler->filter('#main-container > div.row > div.col-md-8 > div.row a span.glyphicon-chevron-left')->count();
        $this->assertTrue($btn_count == 1, "Expected one button with left chevron for last stop. Found :$btn_count");
    }
}
