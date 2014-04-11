<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class LayoutControllerTest extends AbstractControllerTest
{
    private function getViewRoute($externalNetworkId = Fixture::EXTERNAL_NETWORK_ID)
    {
        return $this->generateRoute(
            'canal_tp_mtt_layouts', 
            // fake params since we mock navitia
            array(
                'externalNetworkId' => $externalNetworkId,
            )
        );
    }
    
    public function testList()
    {
        $route = $this->getViewRoute();
        $crawler = $this->doRequestRoute($route);
        $count = $crawler->filter('#main-container table.table tbody tr')->count();
        $this->assertTrue($count == 2, "Found $count layouts. Expected 2");
        $route = $this->getViewRoute('network:Agglobus');
        $crawler = $this->doRequestRoute($route);
        $count = $crawler->filter('#main-container table.table tbody tr')->count();
        $this->assertTrue($count == 1, "Found $count. Expected 1");
    }
}