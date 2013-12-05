<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

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

        $rootNode
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                            ->append($this->addIndexerNode())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Add an indexer config node
     */
    public function addIndexerNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('indexer');

        $node
            ->children()
                ->scalarNode('service_name')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('options')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('events')
                    ->children()
                        ->scalarNode('create')->end()
                        ->scalarNode('update')->end()
                        ->scalarNode('delete')->end()
                    ->end()
                ->end()
                ->scalarNode('event_element')->end()
            ->end()
        ;

        return $node;
    }
}
