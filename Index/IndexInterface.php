<?php

namespace Tms\Bundle\SearchBundle\Index;

interface IndexInterface
{
    public function search($slug, $isIdOnlyFieldToBeReturned);

    public function index($object);

    public function delete($id);

    public function get($id);
}
