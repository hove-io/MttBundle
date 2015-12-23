<?php

namespace CanalTP\MttBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('canal_tp_mtt');

        $rootNode
            ->children()
                ->arrayNode('pdf_generator')
                    ->info('Pdf Generator configuration')
                    ->children()
                        ->scalarNode('class')->defaultValue(
                            'CanalTP\MttBundle\Services\PdfGenerator'
                        )->end()
                        ->scalarNode('server')->defaultValue(
                            'http://localhost'
                        )->end()
                    ->end()
                ->end()
                ->scalarNode('hour_offset')
                    ->info('Hour used for day switching in navitia route_schedules')
                    ->defaultValue(4)
                ->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        return $treeBuilder;
    }
}
