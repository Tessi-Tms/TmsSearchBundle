<?php

namespace Tms\Bundle\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TestController extends Controller
{
    /**
     * @Route("search/{query}")
     */
    public function searchAction($query)
    {
        $searchIndexHandler = $this->container->get('tms_search.handler');
        //$documentManager = $this->container->get('doctrine_mongodb.odm.custom_document_manager');

        $data = $searchIndexHandler->search('tms_participation', $query);
        die(var_dump($data));

        $i = 1;
        $hasNext = true;
        while (true === $hasNext) {
            $data = $searchIndexHandler->searchAndFetchDocument('tms_participation', $query, $i);
            if (false === $data['hasNext']) {
                $hasNext = false;
            }
            $i++;
            var_dump($data);
        }
        die('end');



        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $participations = $participationHandler->retrieveRawParticipations(
                null, null, null, null, null, null, null, null,
                null, null, null, null, null, null, $query);
        die(var_dump($participations));
    }

    /**
     * @Route("create/{id}")
     */
    public function createAction($id)
    {
        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $mongoId = new \MongoId($id);
        $participation = $participationHandler->retrieveParticipation($mongoId);
        $searchHandler = $this->container->get('tms_search.handler');
        die(var_dump($searchHandler->index($participation)));
    }

    /**
     * @Route("update/{id}")
     */
    public function updateAction($id)
    {
        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $mongoId = new \MongoId($id);
        $participation = $participationHandler->retrieveParticipation($mongoId);
        $searchHandler = $this->container->get('tms_search.handler');
        die(var_dump($searchHandler->index($participation)));
    }

    /**
     * @Route("delete/{id}")
     */
    public function deleteAction($id)
    {
        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $mongoId = new \MongoId($id);
        $participation = $participationHandler->retrieveParticipation($mongoId);
        $searchHandler = $this->container->get('tms_search.handler');
        die(var_dump($searchHandler->unIndex($participation)));
    }
}
