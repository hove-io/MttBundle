<?php

namespace CanalTP\MttBundle\Menu;

use Symfony\Component\HttpFoundation\RequestStack;
use CanalTP\MttBundle\Services\UserManager;
use CanalTP\MttBundle\Menu\BusinessMenuItem;
use Symfony\Component\DependencyInjection\Container;

/**
 * Description of MenuManager
 *
 * @author Kévin Ziemianski <kevin.ziemianski@canaltp.fr>
 */
class MenuManager
{
    private $userManager;
    private $container;
    private $requestStack;

    public function __construct(
        UserManager $userManager,
        Container $container
    ) {
        $this->userManager = $userManager;
        $this->container = $container;
        $this->requestStack = $container->get('request_stack');
    }

    public function getMenu()
    {
        $userManager = $this->userManager;
        $userNetworks = $userManager->getNetworks();

        $currentExternalNetworkId = $this->requestStack->getCurrentRequest()->attributes->get('externalNetworkId');
        $currentSeasonId = $this->requestStack->getCurrentRequest()->attributes->get('seasonId');
        $translator = $this->container->get('translator');
        $route = $this->requestStack->getCurrentRequest()->get('_route');
        $menu = array();

        if (count($userNetworks) >= 1) {
            $perimeters = new BusinessMenuItem();
            $perimeters->setName($translator->trans('menu.networks'));
            $currentExternalNetworkId = ($currentExternalNetworkId == null) ? $userNetworks->first()->getExternalNetworkId() : $currentExternalNetworkId;
            foreach ($userNetworks as $userNetwork) {
                $explodedId = explode(':', $userNetwork->getExternalNetworkId());
                $perimeter = new BusinessMenuItem();
                $perimeterName = sprintf('%s%s', $explodedId[1], isset($explodedId[2]) ? ':'.$explodedId[2] : '');
                $perimeter->setName($perimeterName);
                $perimeter->setRoute('canal_tp_mtt_homepage');
                $perimeter->setParameters(array('externalNetworkId' => $userNetwork->getExternalNetworkId()));
                if ($currentExternalNetworkId == $userNetwork->getExternalNetworkId()) {
                    $perimeter->setActive();
                }
                $perimeters->addChild($perimeter);
            }

            $menu[] = $perimeters;
        }

        $currentNetwork = is_null($currentExternalNetworkId) ? $userNetworks->first()->getExternalNetworkId() : $currentExternalNetworkId;

        $securityContext = $this->container->get('security.context');
        // season menu
        if ($securityContext->isGranted('BUSINESS_MANAGE_SEASON')) {
            $seasonManager = $this->container->get('canal_tp_mtt.season_manager');
            $perimeterManager = $this->container->get('nmm.perimeter_manager');
            $perimeter = $perimeterManager->findOneByExternalNetworkId(
                $securityContext->getToken()->getUser()->getCustomer(),
                $currentNetwork
            );
            $perimeterSeasons = $seasonManager->findByPerimeter($perimeter);
            $seasons = new BusinessMenuItem();

            if (count($perimeterSeasons) >= 1) {
                $seasons->setName($translator->trans('menu.seasons'));
                $seasons->setRoute(null);
                foreach ($perimeterSeasons as $perimeterSeason) {
                    $season = new BusinessMenuItem();
                    $season->setName($perimeterSeason->getTitle());
                    $season->setRoute('canal_tp_mtt_stop_point_list_defaults');
                    $season->setParameters(array(
                        'externalNetworkId' => $currentNetwork,
                        'seasonId' => $perimeterSeason->getId()
                    ));
                    if ($currentSeasonId == $perimeterSeason->getId()) {
                        $season->setActive();
                    }
                    $seasons->addChild($season);
                }
                $divider = new \CanalTP\MttBundle\Menu\Divider();
                $seasons->addChild($divider);

                $season = new BusinessMenuItem();
                $season->setName($translator->trans('menu.seasons_manage'));
                $season->setRoute('canal_tp_mtt_season_list');
                $season->setParameters(array(
                    'externalNetworkId' => $currentNetwork
                ));
                $seasons->addChild($season);
            } else {
                $seasons->setName($translator->trans('menu.seasons_manage'));
                $seasons->setRoute('canal_tp_mtt_season_list');
                $seasons->setParameters(array(
                    'externalNetworkId' => $currentNetwork
                ));
            }

            $menu[] = $seasons;
        }

        $edit = new BusinessMenuItem();
        $edit->setName($translator->trans('menu.edit_timetables'));
        $edit->setRoute('canal_tp_mtt_stop_point_list_defaults');
        $edit->setParameters(array(
            'externalNetworkId' => $currentNetwork
        ));

        $edit->setRoutePatternForHighlight(array('/.*_stop_point_.*/', '/.*_calendar_.*/', '/.*_timetable_.*/'));

        $menu[] = $edit;

        if ($securityContext->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'))) {
            $area = new BusinessMenuItem();
            $area->setName($translator->trans('menu.area_manage'));
            $area->setRoute('canal_tp_mtt_area_list');
            $area->setParameters(array(
                'externalNetworkId' => $currentNetwork
            ));

            $area->setRoutePatternForHighlight(array('/.*_area_.*/'));

            $menu[] = $area;
        }

        if ($securityContext->isGranted(array('BUSINESS_MANAGE_LAYOUT_CONFIG'))) {
            $layout = new BusinessMenuItem();
            $layout->setName($translator->trans('menu.layouts_manage'));
            $layout->setRoute('canal_tp_mtt_layout_config_list');
            $layout->setParameters(array(
                'externalNetworkId' => $currentNetwork
            ));
            $layout->setRoutePatternForHighlight(array('/.*_layout_config_.*/'));
            $menu[] = $layout;
        }

        // Admin Menu
        if ($securityContext->isGranted('BUSINESS_MANAGE_LAYOUT_MODEL')
            && $securityContext->isGranted('BUSINESS_ASSIGN_MODEL')
        ) {
            $administration = new BusinessMenuItem();
            $administration->setName($translator->trans('menu.administration'));

            $modelAdministration = new BusinessMenuItem();
            $modelAdministration->setName($translator->trans('menu.models_manage'));
            $modelAdministration->setRoute('canal_tp_mtt_model_list');
            $modelAdministration->setParameters(array(
                'externalNetworkId' => $currentNetwork
            ));
            $modelAdministration->setRoutePatternForHighlight(array('/.*_model_.*/'));
            $administration->addChild($modelAdministration);

            $customer = new BusinessMenuItem();
            $customer->setName($translator->trans('menu.assign_models_to_customers'));
            $customer->setRoute('canal_tp_mtt_customer_list');
            $customer->setParameters(array(
                'externalNetworkId' => $currentNetwork
            ));
            $customer->setRoutePatternForHighlight(array('/.*_customer_.*/'));
            $administration->addChild($customer);

            $administration->setRoutePatternForHighlight(array('/.*_customer_.*/', '/.*_model_.*/'));

            $menu[] = $administration;
        }

        return $menu;
    }
}
