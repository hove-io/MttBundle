<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\CustomerTrait;


class FixturesCustomer extends AbstractFixture implements OrderedFixtureInterface
{
    use CustomerTrait;

    public function load(ObjectManager $om)
    {
        $this->addCustomerToApplication($om, 'app-mtt', 'customer-canaltp');
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 3;
    }
}
