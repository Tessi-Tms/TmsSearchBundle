<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\Exception;

class UndefinedRepositoryException extends \Exception
{
    public function __contruct($className)
    {
        return sprintf('The repository of %s class cannot be found', $className);
    }
}