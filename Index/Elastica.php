<?php

namespace Tms\Bundle\SearchBundle\Index;

use Elastica\Type;
use Elastica\Document as Document;
use Elastica\ResultSet;

final class Elastica extends BaseIndex
{
    /**
     * @param string $slug
     * @return ResultSet
     */
    public function search($slug)
    {
        return $this->getIndex()->search($slug);
    }

    public function index($object)
    {
        $type = $this->getIndex()->getType('participation');

        $searchField = json_decode($object->getSearch(), true);

        $document = new Document($object->getId(), $searchField);
        //die(var_dump($document));
        return $type->addDocument($document);
    }

    public function delete($id)
    {
        $type = $this->getIndex()->getType('participation');
        return $type->deleteById($id);
    }

    public function get($id)
    {
        $type = $this->getIndex()->getType('participation');
        return $type->getDocument($id);
    }

    public function bulk($parameters)
    {
    }

}