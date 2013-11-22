<?php

namespace Tms\Bundle\SearchBundle\Index;

use Elastica\Type;
use Elastica\Document as Document;
use Elastica\ResultSet as ResultSet;

final class Elastica extends AbstractIndex
{
    private function cleanResultSet(ResultSet $resultSet)
    {
        $data = array();
        foreach ($resultSet->getResults() as $result) {
            $hit = $result->getHit();
            $object = new \stdClass();
            $object->id = $hit['_id'];
            foreach ($hit['_source'] as $key => $value) {
                $object->$key = $value;
            }
            array_push($data, $object);
        }
        return $data;
    }

    /**
     * @param string $slug
     * @return ResultSet
     */
    public function search($slug)
    {
        $resultSet = $this->getIndex()->search($slug);
        return $this->cleanResultSet($resultSet);
    }

    public function index($object)
    {
        $type = $this->getIndex()->getType('participation');
        $searchField = json_decode($object->getSearch(), true);
        $document = new Document($object->getId(), $searchField);
        return $type->addDocument($document);
    }

    /**
     *
     * @param string $id
     */
    public function delete($id)
    {
        $type = $this->getIndex()->getType('participation');
        return $type->deleteById($id);
    }

    /**
     *
     * @param string $id
     */
    public function get($id)
    {
        $type = $this->getIndex()->getType('participation');
        return $type->getDocument($id);
    }

    public function bulk($documents)
    {
        throw new \Exception('This method can not be executed');
    }
}