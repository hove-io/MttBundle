<?php

namespace CanalTP\MethBundle\Tests\Controller;

class DefaultControllerTest extends AbstractControllerTest
{
    private function getMockedNavitia()
    {
        $navitia = $this->getMockBuilder('CanalTP\MethBundle\Services\Navitia')
            ->setMethods(array('getLinesByMode', 'getStopPointCalendarsData', 'getCalendarStopSchedules', 'getRouteCalendars'))
            ->setConstructorArgs(array(false,false,false))
            ->getMock();

        $navitia->expects($this->any())
            ->method('getLinesByMode')
            ->will($this->returnValue(array()));
            
        $navitia->expects($this->any())
            ->method('getStopPointCalendarsData')
            ->will($this->returnValue(json_decode($this->readStub('calendars.json'))));
            
        $navitia->expects($this->any())
            ->method('getCalendarStopSchedules')
            ->will($this->returnCallback(
                function(){
                    return json_decode(file_get_contents(dirname(__FILE__) . '/stubs/stop_schedules.json'));
                }
            ));

        return $navitia;
    }
    
    private function getViewRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_calendar_view', 
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
        $this->setService('canal_tp_meth.navitia', $this->getMockedNavitia());
        $crawler = $this->client->request('GET', $this->getViewRoute());
        // Vérifie un status spécifique
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Response status NOK:' . $this->client->getResponse()->getStatusCode());
        
        return $crawler;
    }
    
    public function testCalendarsPresentViewAction()
    {
        $crawler = $this->initialization();
        $this->assertTrue($crawler->filter('h3')->count() == 1, 'Expected h3 title.');
        $this->assertTrue($crawler->filter('.nav.nav-tabs > li')->count() == 4, 'Expected 4 calendars.');
    }
    
    public function testCalendarsNamesViewAction()
    {
        $crawler = $this->initialization();
        // comes from the stub
        $calendarsName = array('Semaine scolaire', 'Semaine hors scolaire', "Samedi", "Dimanche et fêtes");
        foreach ($calendarsName as $name) {
            $this->assertTrue($crawler->filter('html:contains("' . $name . '")')->count() == 1, "Calendar $name not found in answer");
        }
    }
    
    public function testHoursConsistencyViewAction()
    {
        $crawler = $this->initialization();
        $nodeValues = $crawler->filter('.grid-time-column > div:first-child')->each(function ($node, $i) {
            return (int)substr($node->text(), 0, strlen($node->text() - 1));
        });
        foreach($nodeValues as $value){
            $this->assertTrue(is_numeric($value), 'Hour not numeric found.');
            $this->assertTrue($value >= 0 && $value < 24, "Hour $value not in the range 0<->23.");
        }
        
    }
    public function testMinutesConsistencyViewAction()
    {
        $crawler = $this->initialization();
        $nodeValues = $crawler->filter('.grid-time-column > div:not(:first-child)')->each(function ($node, $i) {
            $count = preg_match('/^([\d]+)/', $node->text(), $matches);
            if ($count == 1)
                return (int)$matches[0];
            else
                return false;
        });

        foreach($nodeValues as $value){
            $this->assertTrue(is_numeric($value), 'Minute not numeric found.');
            $this->assertTrue($value >= 0 && $value < 60, "Minute $value not in the range 0<->59.");
        }
        
    }
}