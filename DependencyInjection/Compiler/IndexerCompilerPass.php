<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class IndexerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $indexes = $container->getParameter('tms_search.indexes');

        if (!$container->hasDefinition('tms_search.handler')) {
            return;
        }
        $handlerDefinition = $container->getDefinition('tms_search.handler');

        foreach ($indexes as $name => $index) {

            $collectionName = strtolower(substr(strstr($index['class'], ':'), 1));

            if (!$container->hasDefinition($index['indexer']['name'])) {
                return;
            }
            $indexerDefinition = new DefinitionDecorator($index['indexer']['name']);
            $indexerDefinition->addArgument($index['indexer']['options']);
            $indexerDefinition->addMethodCall('setName', array($name));
            $indexerDefinition->addMethodCall('setCollectionName', array($collectionName));

            $container->setDefinition($index['indexer']['name'] . '.' . $collectionName, $indexerDefinition);

            $handlerDefinition->addMethodCall(
                'addIndexer',
                array($collectionName, new Reference($index['indexer']['name'] . '.' . $collectionName))
            );
        }
    }
}