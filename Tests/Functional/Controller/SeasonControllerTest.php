<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class SeasonControllerTest extends AbstractControllerTest
{
    private $title = 'Saison 1';
    private $startDate = '01/04/2015';
    private $endDate = '03/10/2015';

    private function getRoute($route)
    {
        return $this->generateRoute(
            $route,
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID
            )
        );
    }

    private function getEditForm()
    {
        // Check if the form is correctly display
        $route = $this->getRoute('canal_tp_mtt_season_edit');
        $crawler = $this->doRequestRoute($route);

        // Submit form
        $title = 'Centre';
        $form = $crawler->selectButton('Enregistrer')->form();

        $form['mtt_season[title]'] = $this->title;
        $form['mtt_season[startDate]'] = $this->startDate;
        $form['mtt_season[endDate]'] = $this->endDate;

        return $form;
    }

    public function testEditForm()
    {
        $form = $this->getEditForm();
        $crawler = $this->client->submit($form);

        // Check if when we submit form we are redirected
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();

        // Check if the value is saved correctly
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $this->title . '")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $this->startDate . '")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $this->endDate . '")')->count());
    }

    public function testEmptyForm()
    {
        // Check if the form is correctly displayed
        $route = $this->getRoute('canal_tp_mtt_season_edit');
        $crawler = $this->doRequestRoute($route);

        $form = $crawler->selectButton('Enregistrer')->form();
        $crawler = $this->client->submit($form);

        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
        $this->assertGreaterThan(0, $crawler->filter('div.form-group.has-error')->count());
    }

    public function testEndDateSmallerThanStartDate()
    {
        $form = $this->getEditForm();
        $form['mtt_season[endDate]'] = '03/10/1942';
        $crawler = $this->client->submit($form);
        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
        $this->assertGreaterThan(0, $crawler->filter('div.form-group.has-error')->count());
    }

    public function testUniqueConstraintOnSeasonTitleNetworkId()
    {
        $form = $this->getEditForm();
        $form['mtt_season[title]'] = 'Saison 2';
        $form['mtt_season[startDate]'] = '01/04/2016';
        $form['mtt_season[endDate]'] = '02/04/2016';

        $crawler = $this->client->submit($form);
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->submit($form);
        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
        $this->assertGreaterThan(0, $crawler->filter('div.form-group.has-error')->count());
    }

    public function testDatesOverlappingOtherSeason()
    {
        $form = $this->getEditForm();
        $startDate = new \DateTime("now");
        $endDate = new \DateTime("+4 month");
        $form['mtt_season[startDate]'] = $startDate->format('d/m/Y');
        $form['mtt_season[endDate]'] = $endDate->format('d/m/Y');
        $crawler = $this->client->submit($form);
        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
        $this->assertGreaterThan(0, $crawler->filter('.modal-body .alert.alert-danger')->count());
    }

    public function testSeasonPublicationAndUnpublication()
    {
        $route = $this->generateRoute(
            'canal_tp_mtt_season_unpublish',
            array(
                'seasonId' => Fixture::SEASON_ID,
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
            )
        );
        $crawler = $this->doRequestRoute($route, 302);
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(Fixture::SEASON_ID);
        $this->assertFalse($season->getPublished(), "Season was not unpublished.");
        $route = $this->generateRoute(
            'canal_tp_mtt_season_publish',
            array(
                'seasonId' => Fixture::SEASON_ID,
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
            )
        );
        $crawler = $this->doRequestRoute($route, 302);
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(Fixture::SEASON_ID);
        $this->assertTrue($season->getPublished(), "Season was not unpublished.");
    }

    public function testDeleteSeason()
    {
        $route = $this->generateRoute(
            'canal_tp_mtt_season_delete',
            array(
                'seasonId' => Fixture::SEASON_ID,
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
            )
        );
        $crawler = $this->doRequestRoute($route, 302);
        $seasons = $this->getRepository('CanalTPMttBundle:Season')->find(Fixture::SEASON_ID);
        $this->assertTrue(count($seasons) == 0, "Season was not deleted.");
        $lineConfigs = $this->getRepository('CanalTPMttBundle:LineConfig')->findAll();
        $this->assertTrue(count($lineConfigs) == 0, "lineConfig was not deleted.");
        $timetables = $this->getRepository('CanalTPMttBundle:Timetable')->findAll();
        $this->assertTrue(count($timetables) == 0, "timetable was not deleted.");
        $blocks = $this->getRepository('CanalTPMttBundle:Block')->findAll();
        $this->assertTrue(count($blocks) == 0, "block was not deleted.");
        //reload fixtures after Delete
        $this->reloadMttFixtures();
    }
}
