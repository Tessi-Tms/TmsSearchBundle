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
     * {@inheritDoc}
     */
    public function __construct(array $options = array(), $defaultQueryLimit)
    {
        parent::__construct($options, $defaultQueryLimit);

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
                'query_limit' => $this->defaultQueryLimit,
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
        $data['data'] = array();
        $resultSet = $this->client->search($parameters);

        if (isset($resultSet['status']) && 400 === $resultSet['status']) {
            throw new \Exception('Unexpected error');
        }

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
        $parameters['type'] = (!empty($this->options['collection_name']) ? $this->options['collection_name'] : $this->name);
        $parameters['id'] = $element->getId();

        $body = array();
        foreach ($element->getIndexedData() as $fieldToIndex) {
            if (isset($fieldToIndex['options']) &&
                isset($fieldToIndex['options']['type']) &&
                'json' === $fieldToIndex['options']['type']
            ) {
                $data = json_decode($fieldToIndex['value'], true);
                if (null !== $data) {
                    $body = array_merge($body, $data);
                }
            } else {
                $body[$fieldToIndex['key']] = $fieldToIndex['value'];
            }
        }
        $parameters['body'] = $body;

        $resultSet = $this->client->index($parameters);
        if (isset($resultSet['status']) && 400 === $resultSet['status']) {
            throw new \Exception('Unexpected error');
        }

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
        if (isset($resultSet['status']) && 400 === $resultSet['status']) {
            throw new \Exception('Unexpected error');
        }

        if (is_array($resultSet) && isset($resultSet['ok']) && true === $resultSet['ok']) {
            return true;
        }

        return false;
    }
}