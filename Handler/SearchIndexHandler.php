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
use Tms\Bundle\SearchBundle\Exception\UndefinedRepositoryException;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;

class SearchIndexHandler
{
    private $indexers;
    private $classes;
    private $doctrine;
    private $doctrineMongoDB;

    /**
     *
     * @param Registry $doctrine
     * @param ManagerRegistry $doctrineMongoDB
     */
    public function __construct(Registry $doctrine = null, ManagerRegistry $doctrineMongoDB = null)
    {
        $this->indexers = array();
        $this->classes = array();
        $this->doctrine = $doctrine;
        $this->doctrineMongoDB = $doctrineMongoDB;
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
     * @return array $data
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
     *
     * @param Object $manager
     * @param string $indexName
     * @param string $query
     * @throws UndefinedRepositoryException
     * @return array $data;
     */
    public function searchAndFetch($manager, $indexName, $query)
    {
        $data = array();
        $results = $this->search($indexName, $query);

        if (!count($results)) {
            return $data;
        }

        $repository = $this->getRepository($manager, $indexName);
        foreach ($results as $result) {
            array_push($data, $repository->findOneById($result['id']));
        }
        return $data;
    }

    /**
     *
     * @param string $indexName
     * @param string $query
     */
    public function searchAndFetchDocument($indexName, $query)
    {
        return $this->searchAndFetch($this->doctrineMongoDB->getManager(), $indexName, $query);
    }

    /**
     *
     * @param string $indexName
     * @param string $query
     */
    public function searchAndFetchEntity($indexName, $query)
    {
        return $this->searchAndFetch($this->doctrine->getEntityManager(), $indexName, $query);
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

    /**
     *
     * @param Object $manager
     * @param string $indexName
     * @return Object
     */
    private function getRepository($manager, $indexName)
    {
        if (!$manager) {
            return null;
        }

        $className = array_search($indexName, $this->classes);
        if (false === $className) {
            return null;
        }

        $repository = $manager->getRepository($className);
        if (!$repository) {
            throw new UndefinedRepositoryException($className);
        }

        return $repository;
    }
}
