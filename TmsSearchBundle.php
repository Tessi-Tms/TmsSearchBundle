<?php

namespace Tms\Bundle\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tms\Bundle\SearchBundle\DependencyInjection\TmsSearchExtension;

class TmsSearchBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new TmsSearchExtension();
    }
}
