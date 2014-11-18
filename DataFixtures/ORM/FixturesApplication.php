<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamCoreBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\SamCoreBundle\DataFixtures\ORM\ApplicationTrait;

class FixturesApplication extends SamBaseFixture
{
    use ApplicationTrait;

    public function load(ObjectManager $om)
    {
        $this->createApplication($om, 'Timetable', '/mtt');
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 1;
    }
}
