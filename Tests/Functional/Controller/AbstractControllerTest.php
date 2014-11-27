<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\AuthenticationEvents;
use CanalTP\SamCoreBundle\Tests\Functional\Controller\BaseControllerTest AS SamBaseTestController;

abstract class AbstractControllerTest extends SamBaseTestController
{
    /**
     * This variable check if the bdd was mocked.
     *
     * @var boolean
     */
    protected static $mockDb = true;

    protected function reloadMttFixtures()
    {
        $this->runConsole("doctrine:fixtures:load", array("--fixtures" => __DIR__ . "/../../DataFixtures", '--append' => null, '-e' => 'test_mtt'));
    }

    private function mockDb()
    {
        $this->runConsole("sam:database:reset", array('-e' => 'test_mtt'));
        $this->reloadMttFixtures();
    }

    protected function logIn()
    {
        parent::logIn('mtt', 'mtt', 'mtt@canaltp.fr', array('ROLE_ADMIN'), 'sam_selected_application', 'mtt');
    }

    public function setUp($login = true)
    {
        $this->client = parent::createClient(array('environment' => 'test_mtt'));
        parent::setUp();

        if (self::$mockDb === true) {
            self::$mockDb = false;

            $this->mockDb();
        }
        if ($login == true)
            $this->logIn();
    }

    protected function getSeason()
    {
        $seasons = $this->getRepository('CanalTPMttBundle:Season')->findAll();

        if (count($seasons) == 0) {
            throw new \RuntimeException('No seasons');
        }

        return array_pop($seasons);
    }

    protected function getTimetable($extRouteId)
    {
        $tt = $this->getRepository('CanalTPMttBundle:Timetable')->findOneByExternalRouteId($extRouteId);

        if (count($tt) == 0) {
            throw new \RuntimeException('No timetable');
        }

        return $tt;
    }

    protected function getCustomer()
    {
        $customer = $this->getRepository('CanalTPNmmPortalBundle:Customer')->findOneByNameCanonical('canaltp');

        if ($customer == NULL) {
            throw new \RuntimeException('No customer');
        }

        return $customer;
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
