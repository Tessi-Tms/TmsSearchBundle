<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;

interface SearchIndexerInterface
{
    /**
     * Search indexed element following to given query
     *
     * @param string $query
     */
    public function search($query);

    /**
     * Create a search index based on the given element
     *
     * @param IndexableElementInterface $element
     */
    public function create(IndexableElementInterface $element);

    /**
     * Delete a search index based on the given element
     *
     * @param IndexableElementInterface $element
     */
    public function delete(IndexableElementInterface $element);
}
