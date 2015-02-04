<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

class DefaultControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        $route = $this->generateRoute('canal_tp_mtt_homepage');
        $crawler = $this->client->request('GET', $route);

        $this->assertTrue($crawler->filter('html:contains("TIMETABLE")')->count() > 0);
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

    public function testNetworkSwitch()
    {
        $route = $this->generateRoute('canal_tp_mtt_homepage');
        $crawler = $this->client->request('GET', $route);
        $firstMenuActive = $crawler->filter('#application-navbar ul.nav.navbar-nav > li.dropdown ul li.first.active')->count();
        $this->assertTrue($firstMenuActive == 1, "First menu item in perimeter menu should be active. Expected 1. Count :$firstMenuActive");
        $route = $this->generateRoute(
            'canal_tp_mtt_homepage',
            array(
                'externalNetworkId' => 'network:Agglobus'
            )
        );
        $crawler = $this->client->request('GET', $route);
        $firstMenuNotActive = $crawler->filter('#application-navbar > nav > div.navbar-header > ul > li.current_ancestor.first.dropdown.open > ul > li.active.first.active')->count();
        $this->assertTrue($firstMenuNotActive == 0, "First menu item in perimeter menu should not be active. Expected 0. Count: $firstMenuActive");
        $menuActive = $crawler->filter('#application-navbar ul.nav.navbar-nav > li.dropdown ul li.active')->count();
        $this->assertTrue($menuActive == 1, "Only one menu item in perimeter menu should not be active. Expected 1. Count :$menuActive");
    }

    public function testMeth190KeepCurrentNetworkInLogoLink()
    {
        $route = $this->generateRoute('canal_tp_mtt_homepage');
        $crawler = $this->client->request('GET', $route);
        $link = $crawler->filter('#application-navbar ul.nav.navbar-nav > li.dropdown ul li a')->eq(1)->link();
        $urlMenu = $link->getUri();
        $crawler2 = $this->client->click($link);
        $logoLink = $crawler2->filter('#application-navbar .navbar-brand a')->eq(0)->link();
        $urlLogo = $logoLink->getUri();
        $this->assertEquals($urlMenu, $urlLogo);
    }
}
