<?php

namespace Tms\Bundle\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Tms\Bundle\SearchBundle\DependencyInjection\TmsSearchExtension;
use Tms\Bundle\SearchBundle\DependencyInjection\Compiler\ProviderCompilerPass;

class TmsSearchBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new TmsSearchExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProviderCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
