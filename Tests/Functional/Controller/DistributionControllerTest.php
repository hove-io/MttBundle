<?php

namespace CanalTP\MttBundle\Tests\Controller;

class DistributionControllerTest extends AbstractControllerTest
{
    private function getViewRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_distribution_list', 
            // fake params since we mock navitia
            array(
                'externalCoverageId' => 'test',
                'externalRouteId' => 'test',
                'externalStopPointId' => 'test'
            )
        );
    }
    
    private function initialization()
    {
        $crawler = $this->client->request('GET', $this->getViewRoute());
        // check response code is 200
        $this->assertEquals(
            200, 
            $this->client->getResponse()->getStatusCode(), 
            'Response status NOK:' . $this->client->getResponse()->getStatusCode()
        );
        
        return $crawler;
    }
    
    public function testListAction()
    {
        $this->initialization();
    }
}