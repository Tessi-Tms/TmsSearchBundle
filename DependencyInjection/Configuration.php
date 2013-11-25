<?php

namespace Tms\Bundle\SearchBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('tms_search');

        /*
        $rootNode
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('class')->isRequired()->end()
                                ->arrayNode('provider')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->arrayNode('options')
                                            ->prototype('variable')->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('mapping')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        */


        $rootNode
            ->children()
                ->scalarNode('engine')
                ->end()
                ->scalarNode('index')
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}
