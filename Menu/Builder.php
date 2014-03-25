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
            array('route' => 'canal_tp_mtt_homepage')
        );

        $networks = $userManager->getNetworks();
        foreach ($networks as $network) {
            $menu['network']->addChild(
                $network['external_id'],
                array('route' => 'canal_tp_mtt_homepage')
            );
        }

        // TODO: Remove this and display menu buttons group in network page
        $menu->addChild(
            "Gestion des saisons",
            array(
                'route' => 'canal_tp_mtt_season_list',
                'routeParameters' => array(
                    'network_id' => $networks[0]['external_id']
                )
            )
        );

        return $menu;
    }
}
