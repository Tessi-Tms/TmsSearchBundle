<?php

namespace Tms\Bundle\SearchBundle\Index;

final class Elasticsearch extends AbstractIndex
{
    private $index;
    private $type;

    private function buildObjectFromResponse($hit)
    {
        $object = new \stdClass();
        $object->id = $hit['_id'];
        foreach ($hit['_source'] as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     *
     * @param string $index
     *
     * @return \Tms\Bundle\SearchBundle\Index\Elasticsearch
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     *
     * @param string $type
     *
     * @return \Tms\Bundle\SearchBundle\Index\Elasticsearch
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     *
     * @param string $slug
     */
    public function search($slug)
    {
        $parameters = array();
        $parameters['body']['query']['query_string']['query'] = $slug;
        $parameters = array_merge($parameters, array('index' => $this->index, 'type' => $this->type));

        $response = $this->getClient()->search($parameters);
        $resultSet = array();
        if (isset($response['hits']) && isset($response['hits']['hits'])) {
            $resultSet = array_map(array($this, 'buildObjectFromResponse'), $response['hits']['hits']);
        }

        return $resultSet;
    }

    public function index($object)
    {
        $parameters = array();
        $parameters['index'] = $this->index;
        $parameters['type']  = $this->type;
        $parameters['id']    = $object->getId();
        $parameters['body']  = json_decode($object->getSearch(), true);

        return $this->getClient()->index($parameters);
    }

    /**
     *
     * @param string $id
     */
    public function delete($id)
    {
        $parameters = array();
        $parameters['id'] = $id;
        $parameters = array_merge($parameters, array('index' => $this->index, 'type' => $this->type));
        $response = $this->getClient()->delete($parameters);
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
        $parameters = array_merge($parameters, array('index' => $this->index, 'type' => $this->type));
        $response = $this->getClient()->get($parameters);
        return $response;
    }

    /**
     *
     * @param \Doctrine\MongoDB\Cursor $documents
     * @param array $fields
     *
     * @return number $i
     */
    public function bulk(\Doctrine\MongoDB\Cursor $documents, array $fields)
    {
        $body = "";
        $i = 0;
        foreach ($documents as $document) {
            $fieldsToIndex = array();
            foreach ($fields as $field) {
                $fieldsToIndex[$field] = $document[$field];
            }
            $index = array('index' => array(
                '_index' => $this->index,
                '_type'  => $this->type,
                '_id'    => $document['_id']->{'$id'})
            );
            $body .= json_encode($index) . "\n" . json_encode($fieldsToIndex) . "\n\n";
            $i++;
        }
        $this->getClient()->bulk(array('body' => $body));
        return $i;
    }
}