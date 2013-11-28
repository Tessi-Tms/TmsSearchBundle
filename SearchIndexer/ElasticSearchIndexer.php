<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

final class ElasticSearchIndexer extends AbstractSearchIndexer
{
    private $client;

    /**
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->initializeClient();
    }

    /**
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver
            ->setRequired(array('host', 'port'))
        ;
    }

    /**
     * Set the indexer client according to the options
     */
    protected function initializeClient()
    {
        $this->client = new \Elasticsearch\Client(array(
            'hosts' => array(sprintf('%s:%d',
                $this->options['host'],
                $this->options['port']
            ))
        ));
    }

    /**
     *
     * @param string $query
     * @return array $data:
     */
    public function search($query)
    {
        $parameters = array();
        $parameters['body']['query']['query_string']['query'] = $query;
        $parameters = array_merge(
            $parameters,
            array('index' => $this->name,
                  'type' => (!empty($this->options['collection_name']) ? $this->options['collection_name'] : $this->name))
        );

        $resultSet = $this->client->search($parameters);

        $data = array();
        if (isset($resultSet['hits']) && isset($resultSet['hits']['hits'])) {
            foreach ($resultSet['hits']['hits'] as $hit) {
                $result = array();
                $result['id'] = $hit['_id'];
                $result = array_merge($result, $hit['_source']);
                array_push($data, $result);
            }
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
        $parameters['type']  = (!empty($this->options['collection_name']) ? $this->options['collection_name'] : $this->name);
        $parameters['id']    = $element->getId();

        $body = array();
        foreach ($element->getIndexedData() as $fieldToIndex) {
            if (isset($fieldToIndex['options']) &&
                isset($fieldToIndex['options']['type']) &&
                'json' === $fieldToIndex['options']['type']
            ) {
                $data = json_decode($fieldToIndex['value'], true);
                $body = array_merge($body, $data);
            } else {
                $body[$fieldToIndex['key']] = $fieldToIndex['value'];
            }
        }
        $parameters['body'] = $body;

        $resultSet = $this->client->index($parameters);
        if (is_array($resultSet) && isset($resultSet['ok']) && true === $resultSet['ok']) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param IndexableElementInterface $element
     * @return boolean
     */
    public function delete(IndexableElementInterface $element)
    {
        $parameters = array();
        $parameters['id'] = $element->getId();
        $parameters = array_merge(
            $parameters,
            array('index' => $this->name,
                  'type' => (!empty($this->options['collection_name']) ? $this->options['collection_name'] : $this->name))
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
    /*
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
    */
}