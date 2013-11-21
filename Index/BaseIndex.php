<?php

namespace Tms\Bundle\SearchBundle\Index;

use Elastica\Index as Index;

abstract class BaseIndex implements IndexInterface
{
    private $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function getIndex()
    {
        return $this->index;
    }
}