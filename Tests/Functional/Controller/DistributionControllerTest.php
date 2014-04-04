<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

class DistributionControllerTest extends AbstractControllerTest
{
    private function getListRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_distribution_list', 
            array(
                'externalNetworkId' => 'network:Filbleu',
                // fake params since we mock navitia
                'lineId'            => 'test',
                'routeId'           => 'test',
                'currentSeasonId'   => '1'
            )
        );
    }

    
    // TODO
    // public function testCalendarsPresentViewAction()
    // {
        // $this->doRequestRoute($route);
    // }
}