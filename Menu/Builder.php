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

        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user != 'anon.') {
            $networks = $userManager->getNetworks($user);
            if (count($networks > 1)) {
                $menu->addChild(
                    "network",
                    array(
                        'label' => $translator->trans('menu.networks'),
                        'route' => 'canal_tp_mtt_homepage'
                    )
                );

                foreach ($networks as $network) {
                    $explodedId = explode(':', $network['external_id']);
                    $childOptions = array(
                        'label' => $explodedId[1],
                        'route' => 'canal_tp_mtt_homepage',
                        'routeParameters' => array(
                            'externalNetworkId' => $network['external_id']
                        )
                    );

                    $menu['network']->addChild(
                        $network['external_id'],
                        $childOptions
                    );
                }
                // set current network as active
                if (isset($options['currentNetwork']) && !empty($options['currentNetwork'])) {
                    $menu->getChild('network')->getChild($options['currentNetwork'])->setAttribute('class', 'active');
                }
            }
            $currentNetwork = isset($options['currentNetwork']) ? $options['currentNetwork'] : $networks[0]['external_id'];
            // TODO: Remove this and display menu buttons group in network page
            if ($this->container->get('security.context')->isGranted('BUSINESS_MANAGE_SEASON')) {
                $menu->addChild(
                    "seasons",
                    array(
                        'route' => 'canal_tp_mtt_season_list',
                        'label' => $translator->trans('menu.seasons_manage'),
                        'routeParameters' => array(
                            'externalNetworkId' => $currentNetwork
                        )
                    )
                );
            }

            $menu->addChild(
                "networks_management",
                array(
                    'route' => 'canal_tp_mtt_network_list',
                    'label' => $translator->trans('menu.networks_manage'),
                )
            );
            $menu->addChild(
                "layouts_management",
                array(
                    'label' => $translator->trans('menu.layouts_manage'),
                    'route' => 'canal_tp_mtt_layouts',
                    'routeParameters' => array(
                        'externalNetworkId' => $currentNetwork
                    )
                )
            );
        }

        return $menu;
    }
}
