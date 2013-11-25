<?php

namespace Tms\Bundle\SearchBundle\Index;


abstract class AbstractIndex implements IndexInterface
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    protected function getClient()
    {
        return $this->client;
    }
}