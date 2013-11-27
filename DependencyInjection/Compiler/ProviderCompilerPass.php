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

class ProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $indexes = $container->getParameter('tms_search.indexes');

        if (!$container->hasDefinition('tms_search.handler')) {
            return;
        }
        $handlerDefinition = $container->getDefinition('tms_search.handler');

        foreach ($indexes as $name => $index) {
            $collectionName = str_replace(':', '', $index['class']);
            $clientDefinition = new DefinitionDecorator($index['provider']['name']. '.client');
            $clientDefinition->addArgument(
                array('hosts' => array($index['provider']['options']['host'] . ':' . $index['provider']['options']['port']))
            );
            $container->setDefinition($index['provider']['name']. '.client.' . $collectionName, $clientDefinition);

            if (!$container->hasDefinition($index['provider']['name'])) {
                return;
            }
            $providerDefinition = new DefinitionDecorator($index['provider']['name']);
            $providerDefinition->replaceArgument(0, new Reference($index['provider']['name']. '.client.' . $collectionName));
            $providerDefinition->addMethodCall('setName', array($name));
            $providerDefinition->addMethodCall('setCollectionName', array($collectionName));

            $container->setDefinition($index['provider']['name'] . '.' . $collectionName, $providerDefinition);

            $handlerDefinition->addMethodCall(
                'addIndexer',
                array($collectionName, new Reference($index['provider']['name'] . '.' . $collectionName))
            );
        }
    }
}