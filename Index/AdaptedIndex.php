<?php

namespace Tms\Bundle\SearchBundle\Index;

class AdaptedIndex
{
    private $indexes;

    public function __construct()
    {
        $this->indexes = array();
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @param object $index
     *
     * @return \Tms\Bundle\SearchBundle\Index\AdaptedIndex
     */
    public function addIndex($name, $type, $index = null)
    {
        if (empty($name) || empty($type) || null === $index) {
            throw new \Exception('Not a valid index');
        }
        $this->indexes[$name][$type] = $index;

        return $this;
    }

    public function getIndex($name, $type)
    {
        if (!isset($this->indexes[$name]) || !isset($this->indexes[$name][$type])) {
            throw new \Exception('This index does not exist');
        }
        return $this->indexes[$name][$type];
    }
}
