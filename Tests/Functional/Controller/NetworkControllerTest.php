<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class NetworkControllerTest extends AbstractControllerTest
{
    public function testList()
    {
        $route = $this->generateRoute('canal_tp_mtt_network_list');
        $crawler = $this->doRequestRoute($route);
    }
    
    public function testEditForm()
    {
        // Check if the form is correctly display
        $route = $this->generateRoute('canal_tp_mtt_network_edit');
        $crawler = $this->doRequestRoute($route);

        $labelNetworkField = $crawler->filter('select#mtt_network_external_id')->siblings()->filter('label[style="display:none;"]')->count();
        $selectNetworkField = $crawler->filter('select#mtt_network_external_id[style="display:none;"]')->count();

        $this->assertTrue(
            ($selectNetworkField == 1 && $labelNetworkField == 1),
            "field of network should be hidden before we choose coverage. Expected hidden. hident ? :$selectNetworkField"
        );
    }
}
