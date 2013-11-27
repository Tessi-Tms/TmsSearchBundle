<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Tms\Bundle\SearchBundle\Exception\UndefinedMappingMethodException;
use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;

abstract class AbstractSearchIndexer implements SearchIndexerInterface
{
    protected $name;
    protected $collectionName;

    /**
     *
     * @param string $index
     *
     * @return AbstractSearchIndexer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @param string $collectionName
     *
     * @return AbstractSearchIndexer
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;

        return $this;
    }
}