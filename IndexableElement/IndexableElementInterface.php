<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\IndexableElement;

interface IndexableElementInterface
{
    /**
     * Get unique identifier of an indexable element
     *
     * @return string
     */
    public function getId();

    /**
     * Get the mapping of an indexable element
     *
     * @return array
     */
    public function getIndexedData();
}