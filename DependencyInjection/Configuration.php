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
                    //->cannotBeOverwritten()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                            ->append($this->addProviderNode())
                            ->arrayNode('mapping')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    public function addProviderNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('provider');

        $node
            ->children()
                ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('options')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
