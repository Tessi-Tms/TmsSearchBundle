<?php

namespace Tms\Bundle\SearchBundle\Index;


abstract class BaseIndex implements IndexInterface
{
    private $index;

    public function __construct($client)
    {
        $this->index = $client;
    }

    public function getIndex()
    {
        return $this->index;
    }
}