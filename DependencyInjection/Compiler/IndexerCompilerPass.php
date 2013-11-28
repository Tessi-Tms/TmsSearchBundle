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
            $serviceName = $index['indexer']['service_name'];
            if (!$container->hasDefinition($serviceName)) {
                return;
            }
            $indexerDefinition = new DefinitionDecorator($serviceName);
            $indexerDefinition->addArgument($index['indexer']['options']);
            $indexerDefinition->addMethodCall('setName', array($name));

            $indexerServiceName = sprintf('%s.%s', $serviceName, $name);
            $container->setDefinition($indexerServiceName, $indexerDefinition);

            $handlerDefinition->addMethodCall(
                'addIndexer',
                array($name, $index['class'], new Reference($indexerServiceName))
            );
        }
    }
}