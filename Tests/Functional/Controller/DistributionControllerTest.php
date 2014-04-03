<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

class DsitributionControllerTest extends AbstractControllerTest
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

    private function initialization()
    {
        $this->setService('canal_tp_mtt.navitia', $this->getMockedNavitia());
        $crawler = $this->client->request('GET', $this->getListRoute());
        // check response code is 200
        // var_dump($this->client->getResponse());die;
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode(),
            'Response status NOK:' . $this->client->getResponse()->getStatusCode()
        );
        
        return $crawler;
    }
    
    // TODO
    // public function testCalendarsPresentViewAction()
    // {

    // }
}