<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;

final class ElasticSearchIndexer extends AbstractSearchIndexer
{
    private $client;

    public function __construct(\Elasticsearch\Client $client)
    {
        $this->client = $client;
    }

    /**
     *
     * @param string $query
     * @throws \Exception
     *
     * @return array $data:
     */
    public function search(IndexableElementInterface $element, $query)
    {
        $parameters = array();
        $parameters['body']['query']['query_string']['query'] = $query;
        $parameters = array_merge(
            $parameters,
            array('index' => $this->name,
                  'type' => $this->collectionName)
        );

        $resultSet = $this->client->search($parameters);

        $data = array();
        if (isset($resultSet['hits']) && isset($resultSet['hits']['hits'])) {
            $data = $resultSet['hits']['hits'];
            die(var_dump($data));
        }

        return $data;
    }

    /**
     *
     * @param IndexableElementInterface $element
     * @return boolean
     */
    public function create(IndexableElementInterface $element)
    {
        $parameters = array();
        $parameters['index'] = $this->name;
        $parameters['type']  = $this->collectionName;
        $parameters['id']    = $element->getId();

        $body = array();
        foreach ($element->getIndexedData() as $fieldToIndex) {
            if (isset($fieldToIndex['options']['type']) && 'json' === $fieldToIndex['options']['type']) {
                $data = json_decode($fieldToIndex['value'], true);
                $body = array_merge($body, $data);
            } else {
                $body[$fieldToIndex['field']] = $fieldToIndex['value'];
            }
        }
        $parameters['body'] = $body;

        $resultSet = $this->client->index($parameters);
        //die(var_dump($resultSet));
        if (is_array($resultSet) && isset($resultSet['ok']) && true === $resultSet['ok']) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param IndexableElementInterface $element
     */
    public function delete(IndexableElementInterface $element)
    {
        $parameters = array();
        $parameters['id'] = $element->getId();
        $parameters = array_merge(
            $parameters,
            array('index' => $this->name,
                  'type' => $this->collectionName)
        );

        $resultSet = $this->client->delete($parameters);
        if (is_array($resultSet) && isset($resultSet['ok']) && true === $resultSet['ok']) {
            return true;
        }

        return false;
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