<?php

namespace Tms\Bundle\SearchBundle\Index;

class IndexFactory
{
    public function get($container)
    {
        $engine = $container->getParameter('tms_search.engine');
        $index = $container->getParameter('tms_search.index');
        $indexManager = null;
        try {
            if ('elastica' === $engine) {
                $indexManager = $indexManager = new ElasticaIndex($container->get('fos_elastica.index.' . $index));
                if (!$indexManager) {
                    throw new \Exception('Index not available');
                }
            } else {
                throw new \Exception('Search engine not available');
            }
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }

        return $indexManager;
    }
}
