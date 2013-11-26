<?php

namespace Tms\Bundle\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TestController extends Controller
{
    /**
     * @Route("/search/{slug}")
     */
    public function searchAction($slug)
    {
        $adaptedIndexes = $this->container->get('tms_search.adapted_index');
        $adaptedIndex = $adaptedIndexes->getIndex('tms_participations', 'participation');
        $data = $adaptedIndex->search($slug);
        die(var_dump($data));
    }

    /**
     * @Route("/search_mongo/{slug}")
     */
    public function searchMongoAction($slug)
    {
        $participationHandler = $this->container->get('tms_participation.handler.participation');
        $participations = $participationHandler->retrieveRawParticipations(
            null, null, null, null, null, null, null, null,
            null, null, null, null, null, null, $slug);
        die(var_dump($participations));
    }
}
