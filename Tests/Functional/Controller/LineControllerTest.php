<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $crawler = $this->doRequestRoute($this->getFormRoute());

        // Submit empty form
        $form = $crawler->selectButton('Enregistrer')->form();
        unset($form['line_config[layout_config]']);
        $crawler = $this->client->submit($form);
        // Check if when we submit form we are not redirected ie there is an error
        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);

        $crawler = $this->doRequestRoute($this->getFormRoute());
        // Submit form
        $form = $crawler->selectButton('Enregistrer')->form();
        $field = $form->get('line_config[layout_config]');
        $options = $field->availableOptionValues();
        if (count($options) <= 0) {
            throw new \RuntimeException('Only one layout for this network');
        }
        $field->select($options[0]);
        $crawler = $this->client->submit($form);

        // Check if when we submit form we are redirected
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
    }
}
