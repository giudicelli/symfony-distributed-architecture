<?php

namespace giudicelli\DistributedArchitectureBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DistributedArchitectureExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (empty($config['groups'])) {
            $container->setParameter('distributed_architecture.groups', null);
        } else {
            $container->setParameter('distributed_architecture.groups', $config['groups']);
        }
    }
}
