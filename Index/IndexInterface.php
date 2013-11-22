<?php

namespace Tms\Bundle\SearchBundle\Index;

interface IndexInterface
{
    public function search($slug);

    public function index($object);

    public function delete($id);

    public function get($id);

    public function bulk($documents);
}
