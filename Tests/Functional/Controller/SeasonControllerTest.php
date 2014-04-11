<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class SeasonControllerTest extends AbstractControllerTest
{
    private function getRoute($route)
    {
        return $this->generateRoute(
            $route,
            array(
                'network_id' => Fixture::EXTERNAL_NETWORK_ID
            )
        );
    }

    public function testEditForm()
    {
        // Check if the form is correctly display
        $route = $this->getRoute('canal_tp_mtt_season_edit');
        $crawler = $this->doRequestRoute($route);

        // Submit form
        $title = 'Centre';
        $startDate = '01/04/2015';
        $endDate = '03/10/2014';
        $form = $crawler->selectButton('Enregistrer')->form();

        $form['mtt_season[title]'] = $title;
        $form['mtt_season[startDate]'] = $startDate;
        $form['mtt_season[endDate]'] = $endDate;

        $crawler = $this->client->submit($form);

        // Check if when we submit form we are redirected
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();

        // Check if the value is saved correctly
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $title . '")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $startDate . '")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $endDate . '")')->count());
    }

    public function testEditFormWithErrors()
    {
        // Check if the form is correctly display
        $route = $this->getRoute('canal_tp_mtt_season_edit');
        $crawler = $this->doRequestRoute($route);

        $form = $crawler->selectButton('Enregistrer')->form();
        $crawler = $this->client->submit($form);

        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
    }
}
