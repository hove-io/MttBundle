<?php

namespace CanalTP\MethBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client = null;
    protected $stubs_path = null;
    
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