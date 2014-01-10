<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractSearchIndexer implements SearchIndexerInterface
{
    protected $name;
    protected $options;
    protected $defaultQueryLimit;

    /**
     *
     * @param array $options
     * @param integer $defaultQueryLimit
     */
    public function __construct(array $options = array(), $defaultQueryLimit)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        $this->options = $resolver->resolve($options);
        $this->defaultQueryLimit = $defaultQueryLimit;
    }

    /**
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setOptional(array('collection_name', 'query_limit'))
        ;
    }

    /**
     *
     * @param string $name
     *
     * @return AbstractSearchIndexer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return integer
     */
    public function getQueryLimit()
    {
        return $this->options['query_limit'];
    }
}
