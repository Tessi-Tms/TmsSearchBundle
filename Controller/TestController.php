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
        $searchIndexHandler->getIndexers();

        $participation = new \Tms\Bundle\ParticipationBundle\Document\Participation();
        $data = $searchIndexHandler->search($participation, $query);
        die(var_dump($data));

        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $participations = $participationHandler->retrieveRawParticipations(
                null, null, null, null, null, null, null, null,
                null, null, null, null, null, null, $slug);
        die(var_dump($participations));
    }

    /**
     * @Route("index/{id}")
     */
    public function testIndexAction($id)
    {
        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $mongoId = new \MongoId($id);
        $participation = $participationHandler->retrieveParticipation($mongoId);
        $searchHandler = $this->container->get('tms_search.handler');
        var_dump($participation);
        die(var_dump($searchHandler->index($participation)));
    }

    /**
     * @Route("delete/{id}")
     */
    public function testDeleteAction($id)
    {
        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $mongoId = new \MongoId($id);
        $participation = $participationHandler->retrieveParticipation($mongoId);
        $searchHandler = $this->container->get('tms_search.handler');
        var_dump($participation);
        die(var_dump($searchHandler->unIndex($participation)));
    }
}
