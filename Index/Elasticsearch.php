<?php

namespace Tms\Bundle\SearchBundle\Index;


final class Elasticsearch extends AbstractIndex
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
        $parameters['body']['query']['filter']['lastName'] = $slug;
        $parameters = array_merge($parameters, $this->baseParameters);

        $response = $this->getIndex()->search($parameters);
        if (isset($response['hits']) && isset($response['hits']['hits'])) {
            return $response['hits']['hits'];
        }
        return null;
    }

    public function index($object)
    {
        $parameters = array();
        $parameters['index'] = $this->baseParameters['index'];
        $parameters['type']  = 'participation';
        $parameters['id']    = $object->getId();
        $parameters['body']  = json_decode($object->getSearch(), true);

        return $this->getIndex()->index($parameters);
    }

    public function delete($id)
    {
    }

    public function get($id)
    {
    }

    public function bulk($objects)
    {
        $body = "";
        foreach ($objects as $object) {
            $searchFields = json_decode($object->getSearch(), true);
            $index = array('index' => array('_index' => $this->baseParameters['index'],
                                            '_type'  => 'participation',
                                            '_id'    => $object->getId()));
            $body .= json_encode($index) . "\n" . json_encode($searchFields) . "\n\n";
        }
        $this->getIndex()->bulk(array('body' => $body));
    }
}