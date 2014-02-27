<?php

namespace CanalTP\MethBundle\Tests\Controller;

class DefaultControllerTest extends AbstractControllerTest
{
    public function testView()
    {
        $route = $this->generateRoute('canal_tp_meth_calendar_view');
        $crawler = $this->client->request('GET', $route);
        
        $this->assertTrue($crawler->filter('html:contains("MTT")')->count() > 0);
    }
}