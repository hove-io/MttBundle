<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class BlockControllerTest extends AbstractControllerTest
{
    private function getFormRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_block_edit', 
            // fake params since we mock navitia
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'block_type' => BlockRepository::CALENDAR_TYPE,
                'dom_id' => 'dom_id',
                'timetableId' => 1,
                'stop_point' => false
            )
        );
    }
    
    public function setUp()
    {
        parent::setUp();
    }
    
    // METH-120
    public function testCalendarsBySeason()
    {
        $navitiaMock = $this->getMockedNavitia();
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(1);
        $navitiaMock->expects($this->any())
            ->method('getRouteCalendars')
            ->with(
                $this->anything(), 
                $this->equalTo(Fixture::EXTERNAL_ROUTE_ID), 
                $this->equalTo($season->getStartDate()), 
                $this->equalTo($season->getEndDate())
            )
            ->will($this->returnValue(array()));
            
        $this->setService('canal_tp_mtt.navitia', $navitiaMock);
        $crawler = $this->doRequestRoute($this->getFormRoute());
        
    }
}
