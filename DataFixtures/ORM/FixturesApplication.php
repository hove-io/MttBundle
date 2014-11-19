<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\ApplicationTrait;

class FixturesApplication extends AbstractFixture implements OrderedFixtureInterface
{
    use ApplicationTrait;

    public function load(ObjectManager $om)
    {
        $this->createApplication($om, 'Timetable', '/mtt', 'mtt');
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 1;
    }
}
