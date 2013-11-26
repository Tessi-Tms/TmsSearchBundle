<?php

namespace Tms\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $indexes = $container->getParameter('tms_search.indexes');

        if (!$container->hasDefinition('tms_search.adapted_index')) {
            return;
        }
        $adaptedIndexDefinition = $container->getDefinition('tms_search.adapted_index');

        foreach ($indexes as $indexName => $index) {

            foreach ($index as $typeName => $type) {

                $clientDefinition = $container->getDefinition($type['provider']['name']. '.client');
                $clientDefinition->addArgument(
                    array('hosts' => array($type['provider']['options']['host'] . ':' . $type['provider']['options']['port']))
                );

                if (!$container->hasDefinition($type['provider']['name'])) {
                    return;
                }
                $providerDefinition = $container->getDefinition($type['provider']['name']);
                $providerDefinition->replaceArgument(0, new Reference($type['provider']['name']. '.client'));
                $providerDefinition->addMethodCall('setIndex', array($indexName));
                $providerDefinition->addMethodCall('setType', array($typeName));

                $adaptedIndexDefinition->addMethodCall(
                    'addIndex',
                    array($indexName, $typeName, new Reference($type['provider']['name']))
                );

            }

        }
    }
}