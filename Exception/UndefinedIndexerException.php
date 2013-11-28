<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\Exception;

class UndefinedIndexerException extends \Exception
{
    public function __contruct($class)
    {
        return sprintf('The %s class does not have an indexer', $class);
    }
}