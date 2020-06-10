<?php

namespace giudicelli\DistributedArchitectureBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Load this bundle.
 *
 * @internal
 *
 * @author Frédéric Giudicelli
 */
class DistributedArchitectureExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (class_exists(Application::class)) {
            $loader->load('console.xml');
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (empty($config['groups'])) {
            $container->setParameter('distributed_architecture.groups', null);
        } else {
            $container->setParameter('distributed_architecture.groups', $config['groups']);
        }
        if (empty($config['queue_groups'])) {
            $container->setParameter('distributed_architecture.queue_groups', null);
        } else {
            $container->setParameter('distributed_architecture.queue_groups', $config['queue_groups']);
        }
        if (empty($config['save_states'])) {
            $container->setParameter('distributed_architecture.save_states', false);
        } else {
            $container->setParameter('distributed_architecture.save_states', true);
        }
    }
}
