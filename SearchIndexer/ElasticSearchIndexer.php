<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Tms\Bundle\SearchBundle\IndexableElement;

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
    public function search($query)
    {
        if (empty($query)) {
            throw new \Exception('Not a valid query');
        }

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
            //$data = array_map(array($this, 'buildObjectFromResultSet'), $resultSet['hits']['hits']);
        }

        return $data;
    }

    public function create(IndexableElementInterface $element)
    {
        $parameters = array();
        $parameters['index'] = $this->index;
        $parameters['type']  = $this->type;
        $parameters['id']    = $element->getId();
        $parameters['body']  = json_decode($element->getIndexedData(), true);

        return $this->client->index($parameters);
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
        $data = $this->client->delete($parameters);

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