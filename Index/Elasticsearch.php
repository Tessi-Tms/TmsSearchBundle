<?php

namespace Tms\Bundle\SearchBundle\Index;

final class Elasticsearch implements IndexInterface
{
    private $client;
    private $index;
    private $type;
    private $mapping;

    public function __construct(\Elasticsearch\Client $client)
    {
        $this->client = $client;
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

    public function setMapping($mapping)
    {
        $this->mapping = mapping;

        return $this;
    }

    /**
     *
     * @param array $hit
     *
     * @return \stdClass $object
     */
    private function buildObjectFromResultSet(array $hit)
    {
        $object = new \stdClass();
        $object->id = $hit['_id'];
        if (isset($hit['_source'])) {
            foreach ($hit['_source'] as $key => $value) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    /**
     *
     * @param string $query
     * @param boolean $isIdOnlyFieldToBeReturned
     * @throws \Exception
     *
     * @return array $data:
     */
    public function search($query, $isIdOnlyFieldToBeReturned = true)
    {
        if (empty($query)) {
            throw new \Exception('Not a valid query');
        }

        $parameters = array();
        if (true === $isIdOnlyFieldToBeReturned) {
            $parameters['body']['fields'] = array('id');
        }
        $parameters['body']['query']['query_string']['query'] = $query;
        $parameters = array_merge(
            $parameters,
            array('index' => $this->index,
                  'type' => $this->type)
        );

        $resultSet = $this->client->search($parameters);

        $data = array();
        if (isset($resultSet['hits']) && isset($resultSet['hits']['hits'])) {
            $data = array_map(array($this, 'buildObjectFromResultSet'), $resultSet['hits']['hits']);
        }

        return $data;
    }

    public function index($object)
    {
        $parameters = array();
        $parameters['index'] = $this->index;
        $parameters['type']  = $this->type;
        $parameters['id']    = $object->getId();
        $parameters['body']  = json_decode($object->getSearch(), true);

        return $this->client->index($parameters);
    }

    /**
     *
     * @param string $id
     */
    public function delete($id)
    {
        if (empty($id)) {
            throw new \Exception('Not a valid id');
        }
        $parameters = array();
        $parameters['id'] = $id;
        $parameters = array_merge(
            $parameters,
            array('index' => $this->index,
                  'type' => $this->type)
        );
        $data = $this->client->delete($parameters);

        return $data;
    }

    /**
     *
     * @param string $id
     */
    public function get($id)
    {
        if (empty($id)) {
            throw new \Exception('Not a valid id');
        }
        $parameters = array();
        $parameters['id'] = $id;
        $parameters = array_merge(
            $parameters,
            array('index' => $this->index,
                  'type' => $this->type)
        );
        $data = $this->client->get($parameters);

        return $data;
    }

    /**
     *
     * @param \Doctrine\MongoDB\Cursor $documents
     * @param array $fields
     *
     * @return integer $i
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
        $this->client->bulk(array('body' => $body));
        return $i;
    }
}