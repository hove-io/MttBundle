<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

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
        $crawler = $this->client->request('GET', $route);
        
        $this->assertTrue($crawler->filter('.panel-group > .panel')->count() > 0, "Expected at least one mode for this user.");
    }
}
