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
        $indexManager = $this->container->get('index_manager');

        $data = $indexManager->search($slug);
        die(var_dump($data));
    }

}
