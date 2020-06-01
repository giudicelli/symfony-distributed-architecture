<?php

namespace giudicelli\DistributedArchitectureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Parse the configuration from YAML to a clean array.
 *
 * @internal
 *
 * @author Frédéric Giudicelli
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('distributed_architecture');

        $this->handleRoot($treeBuilder->getRootNode());

        return $treeBuilder;
    }

    protected function handleRoot($rootNode): void
    {
        $rootNode
            ->children()
            ->booleanNode('save_states')->defaultTrue()->end()
            ->arrayNode('groups')
            ->info('The list of processes\'s groups.')
            ->arrayPrototype()
            ->children()
            ->scalarNode('command')->isRequired()->cannotBeEmpty()->info('That bin/console command to execute.')->end()
            ->arrayNode('params')->info('A list of key/value parameters that will be passed to the executed *command*.')
            ->prototype('scalar')->end()
            ->end() // params
            ->scalarNode('bin_path')->info('For all local/remote processes, set the binary to be executed, default is PHP_BINARY from the master command.')->end()
            ->scalarNode('path')->info('For all local/remote processes, set the CWD, default is the CWD from the master command.')->end()
            ->integerNode('priority')->info('For all local/remote processes, set their priority. It requires the master command to be executed as root and the remote processes as well.')->min(-19)->max(19)->defaultValue(0)->end()
            ->integerNode('timeout')->info('For all local/remote processes, set their timeout in seconds. This timeout indicates after which duration without any data from the process we should consider it dead and we should restart it.')->min(5)->defaultValue(30)->end()

            ->arrayNode('local')->info('Execute a local instance of the *command*.')
            ->children()
            ->scalarNode('bin_path')->info('Overide the *bin_path* value.')->end()
            ->scalarNode('path')->info('Overide the *path* value.')->end()
            ->integerNode('priority')->info('Overide the *priority* value.')->min(-19)->max(19)->defaultValue(0)->end()
            ->integerNode('timeout')->info('Overide the *timeout* value.')->min(5)->defaultValue(30)->end()
            ->integerNode('instances_count')->info('Set the number of instances of *command* that should be run.')->min(1)->defaultValue(1)->end()
            ->end()
            ->end() // local

            ->arrayNode('remote')->info('The list of remotely executed instances of the *command*.')
            ->requiresAtLeastOneElement()
            ->arrayPrototype()
            ->children()
            ->scalarNode('bin_path')->info('Overide the *bin_path* value.')->end()
            ->scalarNode('path')->info('Overide the *path* value.')->end()
            ->integerNode('priority')->info('Overide the *priority* value.')->min(-19)->max(19)->defaultValue(0)->end()
            ->integerNode('timeout')->info('Overide the *timeout* value.')->min(5)->defaultValue(30)->end()
            ->integerNode('instances_count')->info('Set the number of instances of *command* that should be run.')->min(1)->defaultValue(1)->end()
            ->arrayNode('hosts')->info('Set the list of hosts on which *command* should be run.')->isRequired()
            ->prototype('scalar')->end()
            ->end() // hosts
            ->scalarNode('username')->info('Set the user name that should be used to connect to *hosts*. Default is the username under which the master is run.')->end()
            ->scalarNode('private_key')->info('Set the private key that should be used to connect to *hosts*. Default is ~*username*/.ssh/id_rsa.')->end()
            ->end()
            ->end()
            ->end() // remote

            ->end()
            ->end()
            ->end()
            ->end()
        ;
    }
}
