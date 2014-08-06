<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class LayoutConfigControllerTest extends AbstractControllerTest
{
    private $label = 'My custom layout';
    private $calendarStart = 4;
    private $calendarEnd = 20;
    private $layoutId = 1;

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
        $route = $this->getRoute('canal_tp_mtt_layout_config_edit');
        $crawler = $this->doRequestRoute($route);

        // Submit form
        $form = $crawler->selectButton('Enregistrer')->form();

        $form['layout_config[label]'] = $this->label;
        $form['layout_config[calendarStart]'] = $this->calendarStart;
        $form['layout_config[calendarEnd]'] = $this->calendarEnd;
        $form['layout_config[layout]'] = $this->layoutId;

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $this->label . '")')->count());
    }

    public function testEmptyForm()
    {
        // Check if the form is correctly displayed
        $route = $this->getRoute('canal_tp_mtt_layout_config_edit');
        $crawler = $this->doRequestRoute($route);

        $form = $crawler->selectButton('Enregistrer')->form();
        $crawler = $this->client->submit($form);

        $this->assertFalse($this->client->getResponse() instanceof RedirectResponse);
        $this->assertGreaterThan(0, $crawler->filter('div.form-group.has-error')->count());
    }
}
