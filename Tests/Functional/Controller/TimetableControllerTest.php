<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class TimetableControllerTest extends AbstractControllerTest
{
    public function setUp($login = true)
    {
        parent::setUp($login);
        $this->setService('canal_tp_mtt.navitia', $this->getMockedNavitia());
    }

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
        $season = $this->getSeason();
        // check on stopPoint page
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $season->getId()));
        $this->checkBlockAndDates($crawler, $season);
        // check on route page
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $season->getId(), false));
        $this->checkBlockAndDates($crawler, $season);
    }


    //List all stop-points default page
    public function testStoppointsList()
    {
        parent::setUp();

        $route = $this->client->getContainer()->get('router')->generate(
            'canal_tp_mtt_stop_point_list',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'line_id' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'seasonId' => $this->getSeason()->getId()
            )
        );
        $crawler = $this->client->request('GET', $route);
        $crawler = $this->doRequestRoute($route, 200);
        $this->checkContextualBtnsPresence($crawler);
    }

    //METH-297
    public function checkContextualBtnsPresence($crawler)
    {
        $stopPointfirstRow = $crawler->filter('table tbody tr')->first();
        $this->assertEquals('Voir les horaires', trim($stopPointfirstRow->filter('td[class="action"] a')->eq(0)->text()));
        $this->assertEquals('Prévisualiser', trim($stopPointfirstRow->filter('td[class="action"] a')->eq(1)->text()));
    }

    // Stop-point timetable preview page
    public function testAnonymousAccess()
    {
        $route = $this->client->getContainer()->get('router')->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalLineId' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => Fixture::EXTERNAL_STOP_POINT_ID,
                'seasonId' => $this->getSeason()->getId(),
                'customerId' => $this->getCustomer()->getId()
            )
        );
        $crawler = $this->client->request('GET', $route);
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode(),
            'Response status NOK:' . $this->client->getResponse()->getStatusCode()
        );
    }

    public function testAccess()
    {
        parent::setUp(false);

        $route = $this->client->getContainer()->get('router')->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalLineId' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => Fixture::EXTERNAL_STOP_POINT_ID,
                'seasonId' => $this->getSeason()->getId()
            )
        );
        $crawler = $this->client->request('GET', $route);
        $this->assertEquals(
            302,
            $this->client->getResponse()->getStatusCode(),
            'Response status NOK:' . $this->client->getResponse()->getStatusCode()
        );
    }

    private function checkCodeBlockInTimetableViewPage($translator, $seasonId)
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $seasonId));

        $this->assertEquals(
            '654895',
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code totem not found in stop point timetable view page"
        );

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_edit', $seasonId, false));
        $this->assertEquals(
            $translator->trans('stop_point.block.code.default', array(), 'default'),
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );
    }

    private function checkCodeBlockInTimetableEditPage($translator, $seasonId)
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $seasonId, null));
        $this->assertNotEmpty(
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_edit', $seasonId, null));
        $this->assertEquals(
            $translator->trans('stop_point.block.code.default', array(), 'default'),
            $crawler->filter('div#text_block_4 div.content')->text(),
            "Stop point code (external code) not found in stop point timetable view page"
        );
    }

    public function testStopPointCodeBlock()
    {
        $translator = $this->client->getContainer()->get('translator');
        $season = $this->getSeason();

        $this->checkCodeBlockInTimetableViewPage($translator, $season->getId());
        $this->checkCodeBlockInTimetableEditPage($translator, $season->getId());
    }

    private function checkPoisBlockInTimetableViewPage($translator, $seasonId)
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $seasonId, null));
        $this->assertNotEmpty(
            $crawler->filter('div#text_block_3 div.content')->text(),
            "Pois not found timetable view page"
        );

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_edit', $seasonId, false));
        $this->assertEquals(
            $translator->trans('stop_point.block.pois.default', array(), 'default'),
            $crawler->filter('div#text_block_3 div.content')->text(),
            "Stop point pois default text not found in stop point timetable view page"
        );
    }

    private function checkPoisBlockInTimetableEditPage($translator, $seasonId)
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $seasonId, null));
        $this->assertNotEmpty(
            $crawler->filter('div#text_block_3 div.content')->text(),
            "Pois not found in stop point timetable view page"
        );

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_edit', $seasonId, false));
        $this->assertEquals(
            $translator->trans('stop_point.block.pois.default', array(), 'default'),
            $crawler->filter('div#text_block_3 div.content')->text(),
            "Pois default text not found in stop point timetable view page"
        );
    }
    public function testStopPointPoisBlock()
    {
        $translator = $this->client->getContainer()->get('translator');
        $season = $this->getSeason();

        $this->checkPoisBlockInTimetableViewPage($translator, $season->getId());
        $this->checkPoisBlockInTimetableEditPage($translator, $season->getId());
    }

    public function testPoiBlock()
    {
        $translator = $this->client->getContainer()->get('translator');

        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $this->getSeason()->getId()));
        $message = $translator->trans('stop_point.block.pois.empty', array('%distance%' => 400), 'default');

        $this->assertEquals(
            0,
            $crawler->filter('html:contains("' . $message . '")')->count(),
            "Stop point poi (for distance) not work in stop point timetable view page"
        );
    }

    /**
     * Test legend colors
     * @TODO test background color in a timetable
     */
    public function testCalendarExceptionsColors()
    {
        $season = $this->getSeason();

        $tt = $this->getTimetable(Fixture::EXTERNAL_ROUTE_ID);
        $tt->getLineConfig()->getLayoutConfig()->setNotesType(\CanalTP\MttBundle\Entity\LayoutConfig::NOTES_TYPE_COLOR);
        $tt->getLineConfig()->getLayoutConfig()->setNotesColors(
            array(
                '#e44155',
                '#ff794e',
                '#4460c5',
                '#0cc2dd',
                '#6ebf52',
                '#bacd40'
            )
        );
        $block = new \CanalTP\MttBundle\Entity\Block();
        $block->setTitle('Semaine scolaire');
        $block->setContent('idcalendar2');
        $block->setDomId('timegrid_block_2');
        $block->setTypeId('calendar');
        $block->setTimetable($tt);

        $this->getEm()->persist($block);
        $this->getEm()->flush();
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_view', $season->getId()));
        file_put_contents(sys_get_temp_dir().'/content.html', $crawler->html());
        $backgroundColorNote = $crawler->filter('.color-reference')->first()->attr('style');

        $this->assertContains('#e44155', $backgroundColorNote);
    }

    public function testAnnotations()
    {
        $route = $this->client->getContainer()->get('router')->generate(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalLineId' => Fixture::EXTERNAL_LINE_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'externalStopPointId' => Fixture::EXTERNAL_STOP_POINT_ID,
                'seasonId' => $this->getSeason()->getId(),
                'customerId' => $this->getCustomer()->getId()
            )
        );
        $crawler = $this->client->request('GET', $route);

        $annotationNumber = $crawler->filter('.color-reference')->count();
        $this->assertEquals(
            2,
            $annotationNumber,
            'Some annotations are not present (Number: ' . $annotationNumber . ')'
        );
    }
}
