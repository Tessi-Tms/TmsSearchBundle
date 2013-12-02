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

        $resolver
            ->setDefaults(array(
                'query_limit' => 10,
            ))
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
     * @param integer $offset
     * @param integer $limit
     * @return array $data
     */
    public function search($query, $offset = null, $limit = null)
    {
        $parameters = array();
        if (!empty($offset) && is_numeric($offset)) {
            $parameters['body']['from'] = $offset;
        }
        if (!empty($limit) && is_numeric($limit)) {
            $parameters['body']['size'] = $limit;
        }
        $parameters['body']['query']['query_string']['query'] = $query;
        $parameters = array_merge(
            $parameters,
            array('index' => $this->name,
                  'type' => (!empty($this->options['collection_name']) ? $this->options['collection_name'] : $this->name))
        );

        $data = array();
        $resultSet = $this->client->search($parameters);

        if (!isset($resultSet['hits'])) {
            return $data;
        }
        $hits = $resultSet['hits'];

        if (isset($hits['total'])) {
            $data['total'] = $hits['total'];
        }
        if (isset($hits['hits'])) {
            $data['count'] = count($hits['hits']);
            if ($data['count'] > 0) {
                $data['data'] = array();
                foreach ($hits['hits'] as $hit) {
                    $result = array();
                    $result['id'] = $hit['_id'];
                    $result = array_merge($result, $hit['_source']);
                    array_push($data['data'], $result);
                }
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