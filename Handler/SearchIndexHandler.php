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
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

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
    public function search($indexName, $query, $page = 1)
    {
        $limit = $this->getIndexerByIndexName($indexName)->getQueryLimit();
        $offset = null;
        if (!empty($page) && is_numeric($page) && $page >= 1) {
            $offset = $page * $limit - $limit;
        }

        $data = array();
        try {
            $data = $this
                ->getIndexerByIndexName($indexName)
                ->search($query, $offset, $limit)
            ;
            $data['page'] = $page;
            $data['hasNext'] = ($data['total'] > $page * $limit ? true : false);
        } catch (\Exception $e) {
            throw new \Exception('Invalid query');
        }

        return $data;
    }

    /**
     *
     * @param Object $manager
     * @param string $indexName
     * @param string $query
     * @return array $data;
     */
    private function searchAndFetch($manager, $indexName, $query, $page)
    {
        $data = $this->search($indexName, $query, $page);

        if (0 === $data['count']) {
            return $data;
        }

        $repository = $this->getRepository($manager, $indexName);
        $results = array();
        foreach ($data['data'] as $result) {
            array_push($results, $repository->findOneById($result['id']));
        }
        $data['data'] = $results;

        return $data;
    }

    /**
     *
     * @param string $indexName
     * @param string $query
     * @param Object $documentManager
     */
    public function searchAndFetchDocument($indexName, $query, $page = 1, $documentManager = null)
    {
        if (!$documentManager) {
            $documentManager = $this->doctrineMongoDB->getManager();
        }

        return $this->searchAndFetch($documentManager, $indexName, $query, $page);
    }

    /**
     *
     * @param string $indexName
     * @param string $query
     * @param Object $entityManager
     */
    public function searchAndFetchEntity($indexName, $query, $page = 1, $entityManager = null)
    {
        if (!$entityManager) {
            $entityManager = $this->doctrine->getEntityManager();
        }

        return $this->searchAndFetch($entityManager, $indexName, $query, $page);
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
     */
    private function getIndexer(IndexableElementInterface $element)
    {
        return $this->getIndexerByClassName(get_class($element));
    }

    /**
     *
     * @param string $className
     * @throws UndefinedIndexerException
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

    /**
     * Batch index documents
     *
     * @param string $indexName
     * @param OutputInterface $documentManager
     */
    public function batchIndexDocuments($indexName, OutputInterface $output, $documentManager = null)
    {
        if (!$documentManager) {
            $documentManager = $this->doctrineMongoDB->getManager();
        }
        $documentManager->getConnection()->getConfiguration()->setLoggerCallable(null);

        return $this->batchIndex($documentManager, $indexName, $output);
    }

    /**
     *
     * @param string $indexName
     * @param OutputInterface $entityManager
     */
    public function batchIndexEntities($indexName, OutputInterface $output, $entityManager = null)
    {
        if (!$entityManager) {
            $entityManager = $this->doctrine->getEntityManager();
        }

        return $this->batchIndex($entityManager, $indexName, $output);
    }

    /**
     *
     * @param Object $manager
     * @param string $indexName
     * @param OutputInterface $output
     * @return integer $indexedElements
     */
    public function batchIndex($manager, $indexName, OutputInterface $output)
    {
        $repository = $this->getRepository($manager, $indexName);
        $elements = $repository->findAll();
        $i = 0;
        $indexedElements = 0;
        foreach ($elements as $element) {
            $i++;
            $isIndexed = $this->index($element);
            if (true === $isIndexed) {
                $indexedElements++;
            }
            $manager->detach($element);

            if ($output) {
                $message = $i . ' - Current: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' Mb';
                $output->writeln($message);
            }
        }

        return $indexedElements;
    }
}
