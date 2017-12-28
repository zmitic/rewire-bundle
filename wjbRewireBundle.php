<?php

namespace wjb\RewireBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use wjb\RewireBundle\DependencyInjection\RewireAnnotationsPass;

class wjbRewireBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RewireAnnotationsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
