<?php

namespace CanalTP\MttBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mttMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $userManager = $this->container->get('canal_tp_mtt.user');
        $menu = $factory->createItem('root');

        $menu->addChild(
            "network",
            array('route' => 'canal_tp_meth_homepage')
        );

        $networks = $userManager->getNetworks();
        foreach ($networks as $network) {
            $menu['network']->addChild(
                $network['external_id'],
                array('route' => 'canal_tp_meth_homepage')
            );
        }

        return $menu;
    }
}
