<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\CustomerTrait;

class FixturesCustomer extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use CustomerTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $navitiaToken = $this->container->getParameter('nmm.navitia.token');
        $samFixturePerimeters = $this->container->getParameter('sam_fixture_perimeters');

        $this->addCustomerToApplication($om, 'app-mtt', 'customer-canaltp', $navitiaToken);

        foreach($samFixturePerimeters as $key => $value) {
            $this->addPerimeterToCustomer($om, $value['coverage'], $value['network'], 'customer-canaltp');
        }
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
