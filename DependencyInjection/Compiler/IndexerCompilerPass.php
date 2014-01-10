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

        $eventDispatcherDefinition = $container->getDefinition('event_dispatcher');
        $eventListener = $container->getDefinition('tms_search.event.subscriber.indexer');
        $subscribedEvents = array();

        $configuration = $container->getParameter('tms_search');
        $indexes = $configuration['indexes'];
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
            $indexerDefinition
                ->addArgument($index['indexer']['options'])
                ->addArgument($container->getParameter('tms_search_default_query_limit'))
                ->addMethodCall('setName', array($name))
            ;

            $indexerServiceName = sprintf('%s.%s', $serviceName, $name);
            $container->setDefinition($indexerServiceName, $indexerDefinition);

            $handlerDefinition->addMethodCall(
                'addIndexer',
                array($name, $index['class'], new Reference($indexerServiceName))
            );

            if (isset($index['indexer']['events'])) {
                foreach ($index['indexer']['events'] as $action => $eventName) {
                    $subscribedEvents[$action] = $eventName;
                }
            }
        }

        $eventListener->addArgument($subscribedEvents);
        $eventDispatcherDefinition->addMethodCall(
            'addSubscriber',
            array(new Reference('tms_search.event.subscriber.indexer'))
        );

        $doctrine = null;
        $doctrineMongoDB = null;
        if ($container->hasDefinition('doctrine')) {
            $doctrine = new Reference('doctrine');
        }
        if ($container->hasDefinition('doctrine_mongodb')) {
            $doctrineMongoDB = new Reference('doctrine_mongodb');
        }
        $handlerDefinition
            ->addArgument($doctrine)
            ->addArgument($doctrineMongoDB)
        ;
    }
}