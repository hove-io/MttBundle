<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class LineControllerTest extends AbstractControllerTest
{
    private function getFormRoute($line_id = Fixture::EXTERNAL_LINE_ID)
    {
        // Check if the form is correctly display
        return $this->generateRoute(
            'canal_tp_mtt_line_choose_layout',
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalRouteId' => Fixture::EXTERNAL_ROUTE_ID,
                'seasonId' => Fixture::SEASON_ID,
                'line_id' => $line_id
            )
        );
    }

    public function testChoiceLayoutForm()
    {
        $crawler = $this->doRequestRoute($this->getFormRoute('123'));

        // Submit empty form
        $form = $crawler->selectButton('Enregistrer')->form();
        $crawler = $this->client->submit($form);

        // TODO: Check why we have redirection ?
        // Check if when we submit form we are not redirected
        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);

        $crawler = $this->doRequestRoute($this->getFormRoute());
        // Submit form
        $form = $crawler->selectButton('Enregistrer')->form();
        $crawler = $this->client->submit($form);

        // Check if when we submit form we are redirected
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
    }
}
