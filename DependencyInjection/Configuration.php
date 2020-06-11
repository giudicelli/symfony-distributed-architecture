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
        $rootNode = $rootNode
            ->children()
        ;
        $groupsNode = $rootNode
            ->arrayNode('groups')
            ->info('The list of processes groups.')
            ->arrayPrototype()
            ->children()
        ;
        $this->addGroupOptions($groupsNode);

        $queueGroupsNode = $rootNode
            ->arrayNode('queue_groups')
            ->info('The list of feeder/consumers processes groups.')
            ->arrayPrototype()
            ->children()
        ;
        $this->addQueueGroupOptions($queueGroupsNode);
    }

    protected function addGroupOptions($parent): void
    {
        $this->addGroupGenericOptions($parent);
        $this->addLocalProcessOptions($parent);
        $this->addRemoteProcessOptions($parent);
    }

    protected function addQueueGroupOptions($parent): void
    {
        $this->addGroupGenericOptions($parent);

        $local_feeder = $parent
            ->arrayNode('local_feeder')
            ->info('Execute a local instance of the feeder.')
            ->children()
        ;
        $this->addGenericOptions($local_feeder, false);
        $this->addGenericFeederOptions($local_feeder);

        $remote_feeder = $parent
            ->arrayNode('remote_feeder')
            ->info('Execute a remote instance of the feeder.')
            ->children()
        ;
        $this->addGenericRemoteProcessOptions($remote_feeder, false);
        $this->addGenericFeederOptions($remote_feeder);

        $consumers = $parent
            ->arrayNode('consumers')
            ->info('The list of consumer instances.')
            ->children()
        ;
        $local_consumer = $this->addLocalProcessOptions($consumers);
        $this->addGenericConsumerOptions($local_consumer);
        $remote_consumer = $this->addRemoteProcessOptions($consumers);
        $this->addGenericConsumerOptions($remote_consumer);
    }

    protected function addGenericFeederOptions($parent): void
    {
        $parent
            ->integerNode('port')
            ->defaultValue(9999)
            ->info('Set the port the feeder should be listening on.')
        ;
        $parent
            ->scalarNode('bind_to')
            ->defaultValue('localhost')
            ->info('Set the IP the feeder should bind to.')
        ;
    }

    protected function addGenericConsumerOptions($parent): void
    {
        $parent
            ->integerNode('port')
            ->defaultValue(9999)
            ->info('Set the feeder\'s port.')
        ;
        $parent
            ->scalarNode('host')
            ->defaultValue('localhost')
            ->info('Set the feeder\'s host.')
        ;
    }

    protected function addGroupGenericOptions($parent): void
    {
        $parent
            ->scalarNode('command')
            ->isRequired()
            ->cannotBeEmpty()
            ->info('The bin/console *command* to execute.')
        ;
        $parent
            ->arrayNode('params')
            ->info('A list of key/value parameters that will be passed to the executed *command*.')
            ->prototype('scalar')
        ;
        $this->addGenericOptions($parent, true);
    }

    protected function addLocalProcessOptions($parent)
    {
        $local = $parent
            ->arrayNode('local')
            ->info('Execute a local instance of the *command*.')
            ->children()
        ;
        $this->addGenericOptions($local, false);
        $local
            ->integerNode('instances_count')
            ->min(1)
            ->defaultValue(1)
            ->info('Set the number of instances of *command* that should be run.')
        ;

        return $local;
    }

    protected function addRemoteProcessOptions($parent)
    {
        $remote = $parent
            ->arrayNode('remote')
            ->info('The list of remotely executed instances of *command*.')
            ->requiresAtLeastOneElement()
            ->arrayPrototype()
            ->children()
        ;
        $this->addGenericRemoteProcessOptions($remote);
        $remote
            ->integerNode('instances_count')
            ->info('Set the number of instances of *command* that should be run.')
            ->min(1)
            ->defaultValue(1)
        ;

        return $remote;
    }

    protected function addGenericRemoteProcessOptions($parent): void
    {
        $this->addGenericOptions($parent, false);
        $parent
            ->scalarNode('username')
            ->info('Set the user name that should be used to connect to *hosts*. Default is the username under which the master is run.')
        ;
        $parent
            ->scalarNode('private_key')
            ->info('Set the path to the private key that should be used to connect to *hosts*. Default is ~*username*/.ssh/id_rsa.')
        ;
        $parent
            ->arrayNode('hosts')
            ->info('Set the list of hosts on which *command* should be run.')
            ->isRequired()
            ->prototype('scalar')
        ;
    }

    protected function addGenericOptions($parent, bool $group): void
    {
        $parent
            ->scalarNode('bin_path')
            ->info($group ? 'For all local/remote processes, set the binary to be executed, default is PHP_BINARY from the master command.' : 'Overide the *bin_path* value from the parent group.')
        ;
        $parent
            ->scalarNode('path')
            ->info($group ? 'For all local/remote processes, set the CWD, default is the CWD from the master command.' : 'Overide the *path* value from the parent group.')
        ;
        $parent
            ->integerNode('priority')
            ->min(-19)
            ->max(19)
            ->defaultValue(0)
            ->info($group ? 'For all local/remote processes, set their priority. It requires the master command to be executed as root and the remote processes as well.' : 'Overide the *priority* value from the parent group.')
        ;
        $parent
            ->integerNode('timeout')
            ->min(5)
            ->defaultValue(-1)
            ->info($group ? 'For all local/remote processes, set their timeout in seconds. This timeout indicates after which duration without any data from the process we should consider it dead and we should restart it.' : 'Overide the *timeout* value from the parent group.')
        ;
    }
}
