<?php


namespace wjb\RewireBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RewireAnnotationsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
//        $definition = $container->getDefinition('routing.loader.annotation');
//        dump($definition);die;
    }
}