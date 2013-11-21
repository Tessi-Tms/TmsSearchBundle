<?php

namespace Tms\Bundle\SearchBundle\Index;

class IndexFactory
{
    public function get($container)
    {
        $engine = $container->getParameter('tms_search.engine');
        $index = $container->getParameter('tms_search.index');
        $elasticIndex = null;
        try {
            switch ($engine) {
                case 'elastica':
                    $elasticIndex = new Elastica($container->get('fos_elastica.index.' . $index));
                    break;

                case 'elasticsearch':
                    $parameters = array();
                    $parameters['hosts'] = array('localhost:9200');
                    $client = new \Elasticsearch\Client($parameters);
                    $elasticIndex = new Elasticsearch($client);
                    $elasticIndex->setBaseParameters(array('index' => $index, 'type' => 'participation'));
                    break;

                default:
                    throw new \Exception('Search engine not available');
                    break;
            }
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }

        return $elasticIndex;
    }
}
