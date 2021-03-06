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
    public function __contruct($className)
    {
        return sprintf('The %s class does not have an indexer', $className);
    }
}