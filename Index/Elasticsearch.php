<?php

namespace Tms\Bundle\SearchBundle\Index;


final class Elasticsearch extends BaseIndex
{
    private $baseParameters = array();

    public function setBaseParameters(array $parameters)
    {
        $this->baseParameters['index'] = $parameters['index'];
        $this->baseParameters['type'] = $parameters['type'];
    }

    /**
     * @param string $slug
     */
    public function search($slug)
    {
        $parameters = array();
        $parameters['body']['query']['match']['lastName'] = $slug;
        $parameters = array_merge($parameters, $this->baseParameters);

        $response = $this->getIndex()->search($parameters);
        if (isset($response['hits']) && isset($response['hits']['hits'])) {
            return $response['hits']['hits'];
        }
        return null;
    }

    public function index($object)
    {
    }

    public function delete($id)
    {
    }

    public function get($id)
    {
    }

    public function bulk($parameters)
    {
    }

}