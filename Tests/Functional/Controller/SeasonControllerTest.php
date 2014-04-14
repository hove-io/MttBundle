<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class SeasonControllerTest extends AbstractControllerTest
{
    private $title = 'Saison 1';
    private $startDate = '01/04/2015';
    private $endDate = '03/10/2016';
    
    private function getRoute($route)
    {
        return $this->generateRoute(
            $route,
            array(
                'network_id' => Fixture::EXTERNAL_NETWORK_ID
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
        $crawler = $this->client->submit($form);
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->submit($form);
        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
        $this->assertGreaterThan(0, $crawler->filter('div.form-group.has-error')->count());
    }
}
