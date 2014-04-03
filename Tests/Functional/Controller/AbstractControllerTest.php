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
    
    protected function getMockedEm($repository, $className = "aClass")
    {   
        $emMock  = $this->getMock('\Doctrine\ORM\EntityManager',
            array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => $className)));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));
        return $emMock;
     }
    
    protected function getMockedLineConfig()
    {
        $lineConfigMock  = $this->getMock(
            'CanalTP\MttBundle\Entity\LineConfig'
        );
        return $lineConfigMock;
    }
    protected function getMockedTimetable()
    {
        $ttMock  = $this->getMock(
            'CanalTP\MttBundle\Entity\Timetable'
        );
        $ttMock->setLineConfig($this->getMockedLineConfig());
        $ttMock->setExternalRouteId('route:TTR:Nav168');
        
        return $ttMock;
    }
    
    protected function getMockedRepository($repoName, $entity)
    {
        $ttMock  = $this->getMock(
            'CanalTP\MttBundle\Entity\\' . $repoName,
            array('find'), 
            array(), 
            '', 
            false
        );
        $ttMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue($entity));
        
        return $ttMock;
    }
    
    public function setUp()
    {
        ini_set('xdebug.max_nesting_level', 200);
        $this->stubs_path = dirname(__FILE__) . '/stubs/';
        $this->client = static::createClient(
            array(), 
            array(
            'PHP_AUTH_USER' => 'mtt@canaltp.fr',
            'PHP_AUTH_PW'   => 'mtt',
            )
        );
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