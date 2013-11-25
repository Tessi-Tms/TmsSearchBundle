<?php

namespace Tms\Bundle\SearchBundle\Index;

final class Elasticsearch extends AbstractIndex
{
    private $baseParameters = array();

    private function buildObjectFromResponse($hit)
    {
        $object = new \stdClass();
        $object->id = $hit['_id'];
        foreach ($hit['_source'] as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }

    public function setBaseParameters(array $parameters)
    {
        $this->baseParameters['index'] = $parameters['index'];
        $this->baseParameters['type'] = $parameters['type'];
    }

    /**
     *
     * @param string $slug
     */
    public function search($slug)
    {
        $parameters = array();
        $parameters['body']['query']['query_string']['query'] = $slug;
        $parameters = array_merge($parameters, $this->baseParameters);

        $response = $this->getIndex()->search($parameters);
        $resultSet = array();
        if (isset($response['hits']) && isset($response['hits']['hits'])) {
            $resultSet = array_map(array($this, 'buildObjectFromResponse'), $response['hits']['hits']);
        }
        return $resultSet;
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

    /**
     *
     * @param string $id
     */
    public function delete($id)
    {
        $parameters = array();
        $parameters['id'] = $id;
        $parameters = array_merge($parameters, $this->baseParameters);
        $response = $this->getIndex()->delete($parameters);
        return $response;
    }

    /**
     *
     * @param string $id
     */
    public function get($id)
    {
        $parameters = array();
        $parameters['id'] = $id;
        $parameters = array_merge($parameters, $this->baseParameters);
        $response = $this->getIndex()->get($parameters);
        return $response;
    }

    public function bulk($documents)
    {
        $body = "";
        $i = 0;
        foreach ($documents as $document) {
            $i++;
            /*
            $searchFields = json_decode($document->getSearch(), true);
            $index = array('index' => array('_index' => $this->baseParameters['index'],
                    '_type'  => 'participation',
                    '_id'    => $document->getId()));
            $body .= json_encode($index) . "\n" . json_encode($searchFields) . "\n\n";
            */
            $searchFields = json_decode($document['search'], true);
            $index = array('index' => array('_index' => $this->baseParameters['index'],
                                            '_type'  => 'participation',
                                            '_id'    => $document['_id']->{'$id'}));
            $body .= json_encode($index) . "\n" . json_encode($searchFields) . "\n\n";

        }
        $this->getIndex()->bulk(array('body' => $body));
        return $i;
    }
}