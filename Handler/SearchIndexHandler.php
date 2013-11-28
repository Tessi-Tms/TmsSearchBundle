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
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class SearchIndexHandler
{
    protected $indexers;
    protected $entityManager;

    /**
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->indexers = array();
        $this->entityManager = $doctrine->getManager();
    }

    /**
     * Add an indexer
     *
     * @param string $class
     * @param SearchIndexerInterface $indexer
     * @return \Tms\Bundle\SearchBundle\handler\SearchIndexHandler
     */
    public function addIndexer($class, SearchIndexerInterface $indexer)
    {
        $this->indexers[$class] = $indexer;

        return $this;
    }

    /**
     * @param IndexableElementInterface $element
     * @param string $query
     * @return array
     *
     */
    public function search(IndexableElementInterface $element, $query)
    {
        $data = array();
        try {
            $data = $this
                ->getIndexer($element)
                ->search($element, $query)
            ;
        } catch (\Exception $e) {
            return false;
        }

        return data;
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
    protected function getIndexer(IndexableElementInterface $element)
    {
        /*
        $class = 'participation';
        if (isset($this->indexers['participation'])) {

        }
        */
        return $this->indexers['participation'];
        die(var_dump(get_class($element)));
        $classMetadata = $this->entityManager->getClassMetadata(get_class($element));
        //die(var_dump($classMetadata->getName()));
        die(var_dump($classMetadata));
        return $this->indexers[$classMetadata['class']];
    }

    public function getIndexers()
    {
        //print_r($this->indexers);
        var_dump($this->indexers);
        die();
    }
}