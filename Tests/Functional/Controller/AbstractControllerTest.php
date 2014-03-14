<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client = null;
    protected $stubs_path = null;
    
    protected function getMockedNavitia()
    {
        $navitia = $this->getMockBuilder('CanalTP\MttBundle\Services\Navitia')
            ->setMethods(array('getLinesByMode', 'getStopPointCalendarsData', 'getCalendarStopSchedulesByRoute', 'getRouteCalendars'))
            ->setConstructorArgs(array(false,false,false))
            ->getMock();

        $navitia->expects($this->any())
            ->method('getLinesByMode')
            ->will($this->returnValue(array()));
            
        $navitia->expects($this->any())
            ->method('getStopPointCalendarsData')
            ->will($this->returnValue(json_decode($this->readStub('calendars.json'))));
            
        $navitia->expects($this->any())
            ->method('getCalendarStopSchedulesByRoute')
            ->will($this->returnCallback(
                function(){
                    return json_decode(file_get_contents(dirname(__FILE__) . '/stubs/stop_schedules.json'));
                }
            ));

        return $navitia;
    }
    
    public function setUp()
    {
        $this->stubs_path = dirname(__FILE__) . '/stubs/';
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'mtt@canaltp.fr',
            'PHP_AUTH_PW'   => 'mtt',
        ));
    }
    
    protected function readStub($filename)
    {
        return file_get_contents($this->stubs_path . $filename);
    }
    
    protected function generateRoute($route, $params = array())
    {
        return $this->client->getContainer()->get('router')->generate($route, $params);
    }
    
    protected function setService($serviceIdentifier, $service)
    {
        return $this->client->getContainer()->set($serviceIdentifier, $service);
    }
}