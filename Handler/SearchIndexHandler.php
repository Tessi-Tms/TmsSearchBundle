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
use Doctrine\Bundle\DoctrineBundle\Registry;

class SearchIndexHandler
{
    protected $indexers;
    protected $doctrine;

    /**
     *
     * @param unknown $indexableElementFactory
     */
    public function __construct(Registry $doctrine)
    {
        $this->indexers = array();
        $this->doctrine = $doctrine;
    }

    public function addIndexer($class, $indexer)
    {
        $this->indexers[$class] = $indexer;

        return $this;
    }

    /**
     * @param TmsParticipationBundle:Participation
     * @param query
     * @return array
     *
     */
    public function search()
    {

    }

    /**
     * @param IndexableElementInterface $element
     * @return boolean
     */
    public function index($element)
    {
        try {
            $this
                ->getIndexer($element)
                ->create($element)
            ;
        } catch (\Exception $e) {
            return false;
        }
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
    }

    /**
     *
     * @param IndexableElementInterface $element
     * @return SearchIndexerInterface
     */
    protected function getIndexer(IndexableElementInterface $element)
    {
        $classMetadata = $this->entityManager->getClassMetadata(get_class($element));
        die(var_dump($classMetadata));
        return $this->indexers[$classMetadata['class']];
    }

    public function getIndexers()
    {
        print_r($this->indexers);
        die();
        die(var_dump($this->indexers));
    }
}