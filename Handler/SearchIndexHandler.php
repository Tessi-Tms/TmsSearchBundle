<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\handler;

use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;
use Tms\Bundle\SearchBundle\SearchIndexer\SearchIndexerInterface;
use Tms\Bundle\SearchBundle\Exception\UndefinedIndexerException;

class SearchIndexHandler
{
    private $indexers;
    private $classes;

    /**
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct()
    {
        $this->indexers = array();
        $this->classes = array();

    }

    /**
     * Add an indexer
     *
     * @param string $indexName
     * @param string $className
     * @param SearchIndexerInterface $indexer
     * @return \Tms\Bundle\SearchBundle\handler\SearchIndexHandler
     */
    public function addIndexer($indexName, $className, SearchIndexerInterface $indexer)
    {
        $this->classes[$className] = $indexName;
        $this->indexers[$indexName] = $indexer;

        return $this;
    }

    /**
     * @param string $indexName
     * @param string $query
     * @return array
     *
     */
    public function search($indexName, $query)
    {
        $data = array();
        try {
            $data = $this
                ->getIndexerByIndexName($indexName)
                ->search($query)
            ;
        } catch (\Exception $e) {
            return $data;
        }

        return $data;
    }

    /**
     * @param IndexableElementInterface $element
     * @return boolean
     */
    public function index(IndexableElementInterface $element)
    {
        try {
            $this
                ->getIndexer($element)
                ->create($element)
            ;
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param IndexableElementInterface $element
     * @return boolean
     */
    public function update(IndexableElementInterface $element)
    {
        try {
            $this
            ->getIndexer($element)
            ->update($element)
            ;
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param IndexableElementInterface $element
     * @return boolean
     */
    public function unIndex(IndexableElementInterface $element)
    {
        try {
            $this
                ->getIndexer($element)
                ->delete($element)
            ;
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param IndexableElementInterface $element
     * @return SearchIndexerInterface
     */
    private function getIndexer(IndexableElementInterface $element)
    {
        return $this->getIndexerByClassName(get_class($element));
    }

    /**
     *
     * @param string $className
     * @throws UndefinedIndexerException
     * @return SearchIndexerInterface
     */
    private function getIndexerByClassName($className)
    {
        if (!isset($this->classes[$className])) {
            throw new UndefinedIndexerException($className);
        }

        return $this->getIndexerByIndexName($this->classes[$className]);
    }

    /**
     *
     * @param string $indexName
     * @throws UndefinedIndexerException
     * @return SearchIndexerInterface
     */
    private function getIndexerByIndexName($indexName)
    {
        if (!isset($this->indexers[$indexName])) {
            throw new UndefinedIndexerException($indexName);
        }

        return $this->indexers[$indexName];
    }
}
