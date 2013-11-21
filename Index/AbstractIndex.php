<?php

namespace Tms\Bundle\SearchBundle\Index;


abstract class AbstractIndex implements IndexInterface
{
    private $index;

    public function __construct($client)
    {
        $this->index = $client;
    }

    protected function getIndex()
    {
        return $this->index;
    }
}