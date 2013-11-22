<?php

namespace Tms\Bundle\SearchBundle\Index;

class IndexFactory
{
    public function get($container)
    {
        $engine = $container->getParameter('tms_search.engine');
        $index = $container->getParameter('tms_search.index');
        $adaptedIndex = null;
        try {
            switch ($engine) {
                case 'elastica':
                    $adaptedIndex = new Elastica($container->get('fos_elastica.index.' . $index));
                    break;

                case 'elasticsearch':
                    $parameters = array();
                    $parameters['hosts'] = array($container->getParameter('tms_search_host') . ':' . $container->getParameter('tms_search_port'));
                    $client = new \Elasticsearch\Client($parameters);
                    $adaptedIndex = new Elasticsearch($client);
                    $adaptedIndex->setBaseParameters(array('index' => $index, 'type' => 'participation'));
                    break;

                default:
                    throw new \Exception('Search engine not available');
                    break;
            }
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }

        return $adaptedIndex;
    }
}
