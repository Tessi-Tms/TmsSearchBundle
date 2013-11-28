<?php

/**
 *
 * @author: Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\SearchBundle\SearchIndexer;

use Tms\Bundle\SearchBundle\Exception\UndefinedMappingMethodException;
use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractSearchIndexer implements SearchIndexerInterface
{
    protected $name;
    protected $collectionName;
    protected $options;

    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    /**
     *
     * @param string $index
     *
     * @return AbstractSearchIndexer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @param string $collectionName
     *
     * @return AbstractSearchIndexer
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;

        return $this;
    }
}
