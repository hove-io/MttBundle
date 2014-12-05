<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\CustomerTrait;


class FixturesCustomer extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use CustomerTrait;

    /**
    * @var ContainerInterface
    */
    private $container;

    /**
    *{@inheritDoc}
    */
    public function setContainer(ContainerInterface $container = null)
    {
         $this->container = $container;
    }

    public function load(ObjectManager $om)
    {
        $navitiaToken = $this->container->getParameter('nmm.navitia.token');
        $this->addCustomerToApplication($om, 'app-mtt', 'customer-canaltp',$navitiaToken);

        $this->addPerimeterToCustomer($om, 'tisseo', 'network:tisseo', 'customer-canaltp');
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
