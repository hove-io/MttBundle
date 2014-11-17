<?php

namespace CanalTP\MttBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    private function addDivider($menu)
    {
        $menu->addChild(
            "",
            array(
                'attributes' => array('class' => 'divider')
            )
        );
    }

    public function mttMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user != 'anon.') {
            $userManager = $this->container->get('canal_tp_mtt.user');
            $networks = $userManager->getNetworks($user);
            if (count($networks > 1)) {
                $menu->addChild(
                    "network",
                    array(
                        'label' => $translator->trans('menu.networks'),
                        // 'route' => 'canal_tp_mtt_homepage'
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
                if (isset($options['currentExternalNetworkId']) && !empty($options['currentExternalNetworkId'])) {
                    $menu->getChild('network')->getChild($options['currentExternalNetworkId'])->setAttribute('class', 'active');
                }
            }
            $currentNetwork = isset($options['currentExternalNetworkId']) ? $options['currentExternalNetworkId'] : $networks[0]['external_id'];
            // season menu
            if ($this->container->get('security.context')->isGranted('BUSINESS_MANAGE_SEASON')) {
                $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
                    $this->getUser()->getCustomer(),
                    $externalNetworkId
                );
                $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter($perimeter);

                if (count($seasons) > 1) {
                    $menu->addChild(
                        "seasons",
                        array(
                            'label' => $translator->trans('menu.seasons'),
                        )
                    );
                    foreach ($seasons as $season) {
                        $childOptions = array(
                            'label' => $season->getTitle(),
                            'route' => 'canal_tp_mtt_stop_point_list_defaults',
                            'routeParameters' => array(
                                'externalNetworkId' => $currentNetwork,
                                'seasonId' => $season->getId()
                            )
                        );

                        $menu->getChild('seasons')->addChild(
                            "season_" . $season->getId(),
                            $childOptions
                        );
                    }
                    // set current network as active
                    if (isset($options['currentSeasonId']) && !empty($options['currentSeasonId'])) {
                        $menu->getChild('seasons')->getChild("season_" . $options['currentSeasonId'])->setAttribute('class', 'active');
                    }
                    $this->addDivider($menu->getChild('seasons'));
                    $menu->getChild('seasons')->addChild(
                        "seasons_manage",
                        array(
                            'route' => 'canal_tp_mtt_season_list',
                            'label' => $translator->trans('menu.seasons_manage'),
                            'routeParameters' => array(
                                'externalNetworkId' => $currentNetwork
                            )
                        )
                    );
                } else {
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
            }
            $menu->addChild(
                "edit_timetables",
                array(
                    'route' => 'canal_tp_mtt_stop_point_list_defaults',
                    'label' => $translator->trans('menu.edit_timetables'),
                    'routeParameters' => array(
                        'externalNetworkId' => $currentNetwork
                    )
                )
            );
            if ($this->container->get('security.context')->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'))) {
                $menu->addChild(
                    "area_management",
                    array(
                        'label' => $translator->trans('menu.area_manage'),
                        'route' => 'canal_tp_mtt_area_list',
                        'routeParameters' => array(
                            'externalNetworkId' => $currentNetwork
                        )
                    )
                );
            }
            if ($this->container->get('security.context')->isGranted(array('BUSINESS_LIST_LAYOUT_CONFIG', 'BUSINESS_MANAGE_LAYOUT_CONFIG'))) {
                $menu->addChild(
                    "layouts_management",
                    array(
                        'label' => $translator->trans('menu.layouts_manage'),
                        'route' => 'canal_tp_mtt_layout_config_list',
                        'routeParameters' => array(
                            'externalNetworkId' => $currentNetwork
                        )
                    )
                );
            }
        }

        return $menu;
    }
}
