<?php

declare(strict_types=1);

namespace giudicelli\DistributedArchitectureBundle\Tests;

use giudicelli\DistributedArchitectureBundle\Command\MasterCommand;
use giudicelli\DistributedArchitectureBundle\DependencyInjection\DistributedArchitectureExtension;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class QueueCommandTest extends TestCase
{
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = new Logger();
    }

    /**
     * @before
     */
    public function resetLogger()
    {
        $this->logger->reset();
    }

    /**
     * @group local
     */
    public function testLocalOneConsumer(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-queue-command');
        $this->executeCommand($container, $this->logger, ['--max-running-time' => 30]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [localhost] [Feeder - da:my-queue-command/1/1] Available consumers: 1 / 1',
            'debug - [test] [localhost] [Feeder - da:my-queue-command/1/1] Available consumers: 1 / 1',
            'debug - [test] [localhost] [Feeder - da:my-queue-command/1/1] Available consumers: 1 / 1',
            'info - [test] [localhost] [Consumer - da:my-queue-command/2/2] Connected to /tmp/gdaq_117d4fd0db608747bc65ba337b9d0531c6836137.sock',
            'info - [test] [localhost] [Consumer - da:my-queue-command/2/2] MyType:1',
            'info - [test] [localhost] [Consumer - da:my-queue-command/2/2] MyType:2',
            'info - [test] [localhost] [Consumer - da:my-queue-command/2/2] MyType:3',
            'info - [test] [localhost] [Feeder - da:my-queue-command/1/1] Waiting for new connections',
            'notice - [master] Stopping...',
            'notice - [test] [localhost] [Consumer - da:my-queue-command/2/2] Ended',
            'notice - [test] [localhost] [Feeder - da:my-queue-command/1/1] Ended',
            'notice - [test] [localhost] [Feeder - da:my-queue-command/1/1] Feeder queue is empty',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group remote
     */
    public function testRemoteOneConsumer(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-queue-command');
        $this->executeCommand($container, $this->logger, ['--max-running-time' => 30]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.2] Connected to host',
            'debug - [test] [127.0.0.2] Connected to host',
            'debug - [test] [127.0.0.2] Connected to host',
            'debug - [test] [127.0.0.2] Connected to host',
            'debug - [test] [127.0.0.2] [Feeder - da:my-queue-command/1/1] Available consumers: 1 / 1',
            'debug - [test] [127.0.0.2] [Feeder - da:my-queue-command/1/1] Available consumers: 1 / 1',
            'debug - [test] [127.0.0.2] [Feeder - da:my-queue-command/1/1] Available consumers: 1 / 1',
            'info - [test] [127.0.0.2] [Consumer - da:my-queue-command/2/2] Connected to /tmp/gdaq_117d4fd0db608747bc65ba337b9d0531c6836137.sock',
            'info - [test] [127.0.0.2] [Consumer - da:my-queue-command/2/2] MyType:1',
            'info - [test] [127.0.0.2] [Consumer - da:my-queue-command/2/2] MyType:2',
            'info - [test] [127.0.0.2] [Consumer - da:my-queue-command/2/2] MyType:3',
            'info - [test] [127.0.0.2] [Feeder - da:my-queue-command/1/1] Waiting for new connections',
            'notice - [master] Stopping...',
            'notice - [test] [127.0.0.2] Ended',
            'notice - [test] [127.0.0.2] Ended',
            'notice - [test] [127.0.0.2] [Consumer - da:my-queue-command/2/2] Ended',
            'notice - [test] [127.0.0.2] [Feeder - da:my-queue-command/1/1] Ended',
            'notice - [test] [127.0.0.2] [Feeder - da:my-queue-command/1/1] Feeder queue is empty',
            'notice - [test] [127.0.0.2] [master] Received SIGTERM, stopping',
            'notice - [test] [127.0.0.2] [master] Received SIGTERM, stopping',
            'notice - [test] [127.0.0.2] [master] Stopping...',
            'notice - [test] [127.0.0.2] [master] Stopping...',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group mixed
     */
    public function testMixed(): void
    {
        $config = '
distributed_architecture:
    groups:
        First Group:
            command: toto
            local:
    queue_groups:
        First Group:
            command: toto
            local_feeder:
            consumers:
                local:
';
        $container = $this->buildContainer($config);
        $returnValue = $this->executeCommand($container, $this->logger);
        $this->assertEquals(1, $returnValue, 'Command exits with code 1');

        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'critical - Duplicate group name for First Group',
        ];
        $this->assertEquals($expected, $output);
    }

    private function executeCommand(ContainerInterface $container, LoggerInterface $logger, $input = [], $options = []): int
    {
        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $kernel->expects($this->any())->method('getContainer')->willReturn($container);
        $kernel->expects($this->any())->method('getBundles')->willReturn([]);

        /** @var KernelInterface */
        $kernelInterface = $kernel;

        $masterCommand = new MasterCommand();
        $masterCommand->setLogger($logger);

        $application = new Application($kernelInterface);
        $application->add($masterCommand);

        $tester = new CommandTester($application->get('distributed_architecture:run-master'));

        return $tester->execute($input, $options);
    }

    private function buildContainer(string $config): ContainerInterface
    {
        $container = new ContainerBuilder();

        $config = Yaml::parse($config);

        $extension = new DistributedArchitectureExtension();
        $extension->load($config, $container);

        return $container;
    }

    private function buildLocalGroupConfig(string $name, string $command, $count = 1): ContainerInterface
    {
        $config = '
distributed_architecture:
    queue_groups:
        '.$name.':
            command: '.$command.'
            local_feeder:
            consumers:
                local:
                    instances_count: '.$count.'
';

        return $this->buildContainer($config);
    }

    private function buildRemoteGroupConfig(string $name, string $command, $count = 1): ContainerInterface
    {
        $config = '
distributed_architecture:
    queue_groups:
        '.$name.':
            command: '.$command.'
            remote_feeder:
                bind_to: 127.0.0.2
                port: 9999
                hosts:
                    - 127.0.0.2
            consumers:
                remote:
                    -
                        instances_count: '.$count.'
                        host: 127.0.0.2
                        port: 9999
                        hosts:
                            - 127.0.0.2
';

        return $this->buildContainer($config);
    }
}
