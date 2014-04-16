<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class DefaultControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        $route = $this->generateRoute('canal_tp_mtt_homepage');
        $crawler = $this->client->request('GET', $route);

        $this->assertTrue($crawler->filter('html:contains("MTT")')->count() > 0);
    }

    public function testNavigation()
    {
        $route = $this->generateRoute(
            'canal_tp_mtt_menu',
            array(
                'externalNetworkId' => 'network:Filbleu',
                'current_season' => 1
            )
        );
        $crawler = $this->doRequestRoute($route);
        $modes_count = $crawler->filter('ul#lines-accordion > li.mode-wrapper')->count();

        $this->assertTrue($modes_count > 0, "Expected at least one mode for this user. Found :$modes_count");
    }

    private function insertNetwork()
    {
        $fixture = new Fixture();
        $network = $fixture->createNetwork($this->getEm(), 'network:Agglobus', 'Centre');
        $user = $this->getEm()->getRepository('CanalTPSamEcoreUserManagerBundle:User')->find(1);
        $network->addUser($user);
        $this->getEm()->flush();
    }
    public function testNetworkSwitch()
    {
        $route = $this->generateRoute('canal_tp_mtt_homepage');
        $crawler = $this->client->request('GET', $route);
        $this->insertNetwork();
        $firstMenuActive = $crawler->filter('ul.nav.navbar-nav > li.dropdown ul li.first.active')->count();
        $this->assertTrue($firstMenuActive == 1, "First menu item in perimeter menu should be active. Expected 1. Count :$firstMenuActive");
        $route = $this->generateRoute(
            'canal_tp_mtt_homepage',
            array(
                'externalNetworkId' => 'network:Agglobus'
            )
        );
        $crawler = $this->client->request('GET', $route);
        $firstMenuNotActive = $crawler->filter('ul.nav.navbar-nav > li.dropdown ul li.first.active')->count();
        $this->assertTrue($firstMenuNotActive == 0, "First menu item in perimeter menu should not be active. Expected 0. Count :$firstMenuActive");
        $menuActive = $crawler->filter('ul.nav.navbar-nav > li.dropdown ul li.active')->count();
        $this->assertTrue($menuActive == 1, "Only one menu item in perimeter menu should not be active. Expected 1. Count :$menuActive");
    }
}
