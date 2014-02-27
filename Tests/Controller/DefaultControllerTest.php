<?php

namespace CanalTP\MethBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'mtt@canaltp.fr',
            'PHP_AUTH_PW'   => 'mtt',
        ));
    }
    
    private function generateRoute($route)
    {
        return $this->client->getContainer()->get('router')->generate($route, array(), false);
    }
    
    public function testIndex()
    {
        $route = $this->generateRoute('canal_tp_meth_homepage');
        $crawler = $this->client->request('GET', $route);
        
        $this->assertTrue($crawler->filter('html:contains("MTT")')->count() > 0);
    }
    
    public function testNavigation()
    {
        $route = $this->generateRoute('canal_tp_meth_menu');
        $crawler = $this->client->request('GET', $route);
        
        $this->assertTrue($crawler->filter('.panel-group > .panel')->count() > 0);
    }
}
