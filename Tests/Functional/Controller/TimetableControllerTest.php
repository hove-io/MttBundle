<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimetableControllerTest extends AbstractControllerTest
{
    private function getViewRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => 'network:Filbleu',
                'externalLineId' => 'line:TTR:Nav62',
                'externalRouteId' => 'route:TTR:Nav168',
                'externalStopPointId' => 'stop_point:TTR:SP:STPGB-2',
                "seasonId" => 1
            )
        );
    }
    
    protected function runConsole($command, Array $options = array())
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options["-n"] = true;
        $options = array_merge($options, array('command' => $command));
        
        return $this->_application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }
    
    public function setUp()
    {
        parent::setUp();
        
        $kernel = $this->client->getKernel();
        
        $this->_application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $this->_application->setAutoExit(false);
        $this->runConsole("doctrine:schema:drop", array("--force" => true));
        $this->runConsole("doctrine:schema:create");
        $this->runConsole("doctrine:fixtures:load", array("--fixtures" => __DIR__ . "/../../DataFixtures"));
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
    
    public function testIndex()
    {
        $crawler = $this->initialization();
        
    }
}