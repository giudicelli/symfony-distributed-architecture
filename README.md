
# symfony-distributed-architecture ![CI](https://github.com/giudicelli/symfony-distributed-architecture/workflows/CI/badge.svg)

Symfony Distributed Architecture is a Symfony bundle. It extends [distributed-architecture](https://github.com/giudicelli/distributed-architecture) to provide compatibility with the Command system from Symfony.

## Installation

```bash
$ composer require giudicelli/symfony-distributed-architecture
```

## Using

To run your distributed architecture you will mainly need to use one command "bin/console distributed_architecture:run-master". It will parse the configuration and launch all processes.

The following options are handled by "distributed_architecture:run-master":
- --max-running-time will gracefully stop all slave processes after a certain duration. It's usually a good idea to use this as Symfony commands tend to use more and more memory over time. A duration of 3600 seconds is in most case a good value. Default is 0, meaning the master will only exit once all the slaves a exited.
- --max-process-timeout Set the maximum number of times a process can timeout before it is considered dead and removed. Default is 3.
- --timeout Set the timeout for the master. Default is 300.

### Configuration

Place your configuration in "config/packages/distributed_architecture.yaml".

Here is a complete example of a configuration.

```yaml
distributed_architecture:
  save_states: true # Save each process' state in the ProcessStatus entity
  groups:
    First Group: # The name of the group
      command: app:test-command # The command to be executed using bin/console
      bin_path: /usr/bin/php7 # When the binary is not the same as the master's
      path: /the/path/to/symfony # When Symfony's path is not the same as the master's
      priority: -10 # Set all processes' priority, it will require to whole architecture to run as root
      timeout: 60 # We consider a process timed out when not receiving data for this duration
      params: # Parameters that will be passed to all the processes
        Param1: Value1 
        Param2: Value2
      local: # We want to run a local process
        instances_count: 2 # We want to run 2 instances of the command
        bin_path: /usr/bin/php7-3 # We can overide the default value from the group
        path: /the/path/to/symfony4 # We can overide the default value from the group
        priority: 10 # We can overide the default value from the group
        timeout: 120 # We can overide the default value from the group
      remote: # We want to launch remote processes
        - # First remote process
          instances_count: 2  # We want to run 2 instances of the command on each host
          username: otherusername # When we should use another user name that the user used to run the master process
          private_key: /path/to/privateKey/id_rsa # When the private key used to connect is not stored in ~username/.ssh/id_rsa
          bin_path: /usr/bin/php7-3 # We can overide the default value from the group
          path: /the/path/to/symfony4 # We can overide the default value from the group
          priority: 10 # We can overide the default value from the group
          timeout: 120 # We can overide the default value from the group
          hosts: # The list of hosts
            - server-host-1
            - server-host-2
        - # Second remote process
          instances_count: 2  # We want to run 2 instances of the command on each host
          hosts: # The list of hosts
            - server-host-3
    Second Group: # The name of the group
      command: app:test-command-2 # The command to be executed using bin/console
      params: # Parameters that will be passed to all the processes
        Param1: Value1 
        Param2: Value2
      local: # We want to run a local process
        instances_count: 1 # We want to run 2 instances of the command
      remote: # We want to launch remote processes
        - # First remote process
          instances_count: 1  # We want to run 2 instances of the command on each host
          hosts: # The list of hosts
            - server-host-1
            - server-host-2
            - server-host-3
```

The above code creates two groups.

One group is called "First Group" and it will run "bin/console app:test-command":
- 2 instances on the local machine,
- 2 instances on the "server-host-1" machine,
- 2 instances on the "server-host-2" machine,
- 2 instances on the "server-host-3" machine.

A total of 8 instances of "bin/console test-command" will run.

The other group is called "Second Group" and it will run "bin/console app:test-command-2":
- 1 instance on the local machine,
- 1 instance on the "server-host-1" machine,
- 1 instance on the "server-host-2" machine,
- 1 instance on the "server-host-3" machine.

A total of 4 instances of "bin/console test-command-2" will run.

Usually your configuration is the same between your master machine and your slave machines. Meaning:
- the path to Symfony is the same,
- the PHP binary is the same,
- the current username as access to all remote machines using a private key,
- the private key is stored in $HOME/.ssh/id_rsa.

When all those are true, your configuration can be very minimal.

```yaml
distributed_architecture:
  groups:
    First Group: # The name of the group
      command: app:test-command # The command to be executed using bin/console
      params: # Parameters that will be passed to all the processes
        Param1: Value1 
        Param2: Value2
      local: # We want to run a local process
        instances_count: 2 # We want to run 2 instances of the command
      remote: # We want to launch remote processes
        - # First remote process
          instances_count: 2  # We want to run 2 instances of the command on each host
          hosts: # The list of hosts
            - server-host-1
            - server-host-2
        - # Second remote process
          instances_count: 2  # We want to run 2 instances of the command on each host
          hosts: # The list of hosts
            - server-host-3
    Second Group: # The name of the group
      command: app:test-command-2 # The command to be executed using bin/console
      params: # Parameters that will be passed to all the processes
        Param1: Value1 
        Param2: Value2
      local: # We want to run a local process
        instances_count: 1 # We want to run 2 instances of the command
      remote: # We want to launch remote processes
        - # First remote process
          instances_count: 1  # We want to run 2 instances of the command on each host
          hosts: # The list of hosts
            - server-host-1
            - server-host-2
            - server-host-3
```

### Slave command

A slave command must extend the "giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveCommand" class. 

You may not pass it options, the only acceptable options are defined by "AbstractSlaveCommand" and are passed by "distributed_architecture:run-master". If you need to pass it some parameters, please use the "params" entries in the group's configuration.

Using the above example, here is a possible implementation for "app:test-command" or "app:test-command-2".

```php
<?php

namespace App\Command;

use giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveCommand;
use giudicelli\DistributedArchitectureBundle\Handler;

class TestCommand extends AbstractSlaveCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('app:test-command');
        $this->setDescription('Do the work load');
    }

    // This method must be implemented
    protected function runSlave(Handler $handler): void
    {
        $groupConfig = $handler->getGroupConfig();

        $params = $groupConfig->getParams();

        // Handler::sleep will return false if we were
        // asked to stop by the master command
        while($handler->sleep(1)) {
            echo $params['Param1']." ".$params['Param2']."\n";
        }
    }
}

```