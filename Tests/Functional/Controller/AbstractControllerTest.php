<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client = null;
    protected $stubs_path = null;
    protected $with_db = null;

    protected function getMockedNavitia()
    {
        $navitia = $this->getMockBuilder('CanalTP\MttBundle\Services\Navitia')
            ->setMethods(
                array(
                    'findAllLinesByMode',
                    'getStopPointCalendarsData',
                    'getCalendarStopSchedulesByRoute',
                    'getRouteCalendars',
                    'getRouteData'
                )
            )->setConstructorArgs(array(false,false,false))
            ->getMock();

        $navitia->expects($this->any())
            ->method('findAllLinesByMode')
            ->will($this->returnValue(array()));

        $navitia->expects($this->any())
            ->method('getRouteData')
            ->will($this->returnCallback(
                function () {
                    $return = new \stdClass;
                    $return->name = 'toto';

                    return $return;
                }
            ));

        $navitia->expects($this->any())
            ->method('getStopPointCalendarsData')
            ->will($this->returnValue(json_decode($this->readStub('calendars.json'))));

        $navitia->expects($this->any())
            ->method('getRouteCalendars')
            ->will($this->returnValue(json_decode($this->readStub('calendars.json'))));

        $navitia->expects($this->any())
            ->method('getCalendarStopSchedulesByRoute')
            ->will($this->returnCallback(
                function () {
                    return json_decode(file_get_contents(dirname(__FILE__) . '/stubs/stop_schedules.json'));
                }
            ));

        return $navitia;
    }

    protected function doRequestRoute($route, $expectedStatusCode = 200, $method = 'GET')
    {
        $crawler = $this->client->request($method, $route);

        // check response code is expectedStatusCode
        $this->assertEquals(
            $expectedStatusCode,
            $this->client->getResponse()->getStatusCode(),
            'Response status NOK:' . $this->client->getResponse()->getStatusCode() . "\r\n"
        );

        return $crawler;
    }

    private function initConsole()
    {
        $kernel = $this->client->getKernel();
        $this->_application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $this->_application->setAutoExit(false);
    }

    private function mockDb()
    {
        $this->runConsole("doctrine:schema:create");
        // $this->runConsole("doctrine:fixtures:load");
        $this->runConsole("doctrine:fixtures:load", array("--fixtures" => __DIR__ . "/../../DataFixtures"));
    }

    public function setUp($with_db = true)
    {
        $this->with_db = $with_db;
        $this->stubs_path = dirname(__FILE__) . '/stubs/';
        $this->client = static::createClient(
            array(),
            array(
            'PHP_AUTH_USER' => 'mtt@canaltp.fr',
            'PHP_AUTH_PW'   => 'mtt',
            )
        );

        ini_set('xdebug.max_nesting_level', 200);
        $this->initConsole();
        if ($this->with_db) {
            $this->runConsole("doctrine:schema:drop", array("--force" => true));
            $this->mockDb();
        }
    }

    protected function runConsole($command, Array $options = array())
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options["-n"] = true;
        $options = array_merge($options, array('command' => $command));

        return $this->_application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }

    protected function getRepository($repositoryName)
    {
        return $this->getEm()->getRepository($repositoryName);
    }

    protected function getEm()
    {
        return $this->client->getContainer()->get('doctrine.orm.entity_manager');
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

    public function tearDown()
    {
        if ($this->with_db) {
            // $this->runConsole("doctrine:schema:drop", array("--force" => true));
        }
    }
}
