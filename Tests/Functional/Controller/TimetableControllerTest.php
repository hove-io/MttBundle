<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class TimetableControllerTest extends AbstractControllerTest
{
    private function getRoute($route, $seasonId, $externalStopPointId = Fixture::EXTERNAL_STOP_POINT_ID)
    {
        return $this->generateRoute(
            $route,
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalLineId' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => $externalStopPointId,
                "seasonId" => $seasonId
            )
        );
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

    public function testSeasonBlockDates()
    {
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(1);
        // check on stopPoint page
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $season->getId()));
        $this->checkBlockAndDates($crawler, $season);
        // check on route page
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $season->getId(), false));
        $this->checkBlockAndDates($crawler, $season);
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

    private function checkInTimetableViewPage($translator, $seasonId)
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $seasonId));
        $this->assertNotEmpty(
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_edit', $seasonId, false));
        $this->assertEquals(
            $translator->trans('stop_point_code.block.default', array(), 'default'),
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );
    }

    private function checkInTimetableEditPage($translator, $seasonId)
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $seasonId));
        $this->assertNotEmpty(
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_edit', $seasonId, false));
        $this->assertEquals(
            $translator->trans('stop_point_code.block.default', array(), 'default'),
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );
    }

    public function testStopPointCodeBlock()
    {
        $translator = $this->client->getContainer()->get('translator');
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(1);

        $this->checkInTimetableViewPage($translator, $season->getId());
        $this->checkInTimetableEditPage($translator, $season->getId());
    }
}
