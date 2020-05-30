<?php

declare(strict_types=1);

namespace giudicelli\DistributedArchitectureBundle\Tests;

use giudicelli\DistributedArchitectureBundle\Command\MasterCommand;
use giudicelli\DistributedArchitectureBundle\DependencyInjection\DistributedArchitectureExtension;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
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
 */
final class CommandTest extends TestCase
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
    public function testLocalOneInstance(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-command');
        $this->executeCommand($container, $this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'info - [test] [localhost] [da:my-command/1/1] Child clean exit',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group local
     */
    public function testLocalTwoInstances(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-command', 2);
        $this->executeCommand($container, $this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'info - [test] [localhost] [da:my-command/1/1] Child clean exit',
            'info - [test] [localhost] [da:my-command/2/2] Child 2 2',
            'info - [test] [localhost] [da:my-command/2/2] Child clean exit',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
            'notice - [test] [localhost] [da:my-command/2/2] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group local
     */
    public function testLocalPassingParameters(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-command', 1, ['message' => 'New Message']);
        $this->executeCommand($container, $this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'info - [test] [localhost] [da:my-command/1/1] Child clean exit',
            'info - [test] [localhost] [da:my-command/1/1] New Message',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group local
     */
    public function testLocalMaxRunningTime(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-command', 1, ['sleep' => 20]);
        $this->executeCommand($container, $this->logger, ['--max-running-time' => 10]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'info - [test] [localhost] [da:my-command/1/1] Child clean exit',
            'notice - [master] Stopping...',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group local
     */
    public function testLocalTimeoutForContent(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-command', 1, ['forceSleep' => 90]);
        $this->executeCommand($container, $this->logger, ['--timeout' => 5]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'error - [master] Timeout waiting for content, force kill',
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group local
     */
    public function testLocalTimeoutForCleanStop(): void
    {
        $container = $this->buildLocalGroupConfig('test', 'da:my-command', 1, ['neverDie' => true]);
        $this->executeCommand($container, $this->logger, ['--timeout' => 10, '--max-running-time' => 5]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'error - [master] Timeout waiting for clean shutdown, force kill',
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'notice - [master] Stopping...',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @group remote
     */
    public function testRemoteConnectivity(): void
    {
        $returnVar = 0;
        system('ssh -o "UserKnownHostsFile=/dev/null" -o "StrictHostKeyChecking=no" 127.0.0.1 /bin/true', $returnVar);
        $this->assertEquals(0, $returnVar, 'ssh on 127.0.0.1 is working');
    }

    /**
     * @depends testRemoteConnectivity
     * @group remote
     */
    public function testRemoteOneInstance(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-command');
        $this->executeCommand($container, $this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.1] Connected to host',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child 1 1',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child clean exit',
            'notice - [test] [127.0.0.1] Ended',
            'notice - [test] [127.0.0.1] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @depends testRemoteConnectivity
     * @group remote
     */
    public function testRemoteTwoInstances(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-command', 2);
        $this->executeCommand($container, $this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.1] Connected to host',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child 1 1',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child clean exit',
            'info - [test] [127.0.0.1] [da:my-command/2/2] Child 2 2',
            'info - [test] [127.0.0.1] [da:my-command/2/2] Child clean exit',
            'notice - [test] [127.0.0.1] Ended',
            'notice - [test] [127.0.0.1] [da:my-command/1/1] Ended',
            'notice - [test] [127.0.0.1] [da:my-command/2/2] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @depends testRemoteConnectivity
     * @group remote
     */
    public function testRemotePassingParameters(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-command', 1, ['message' => 'New Message']);
        $this->executeCommand($container, $this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.1] Connected to host',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child clean exit',
            'info - [test] [127.0.0.1] [da:my-command/1/1] New Message',
            'notice - [test] [127.0.0.1] Ended',
            'notice - [test] [127.0.0.1] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @depends testRemoteConnectivity
     * @group remote
     */
    public function testRemoteMaxRunningTime(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-command', 1, ['sleep' => 20]);
        $this->executeCommand($container, $this->logger, ['--max-running-time' => 10]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.1] Connected to host',
            'debug - [test] [127.0.0.1] Connected to host',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child 1 1',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child clean exit',
            'notice - [master] Stopping...',
            'notice - [test] [127.0.0.1] Ended',
            'notice - [test] [127.0.0.1] [da:my-command/1/1] Ended',
            'notice - [test] [127.0.0.1] [master] Received SIGTERM, stopping',
            'notice - [test] [127.0.0.1] [master] Stopping...',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @depends testRemoteConnectivity
     * @group remote
     */
    public function testRemoteTimeoutForContent(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-command', 1, ['forceSleep' => 90]);
        $this->executeCommand($container, $this->logger, ['--timeout' => 5]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.1] Connected to host',
            'debug - [test] [127.0.0.1] Connected to host',
            'error - [master] Timeout waiting for content, force kill',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child 1 1',
            'notice - [test] [127.0.0.1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    /**
     * @depends testRemoteConnectivity
     * @group remote
     */
    public function testRemoteTimeoutForCleanStop(): void
    {
        $container = $this->buildRemoteGroupConfig('test', 'da:my-command', 1, ['neverDie' => true]);
        $this->executeCommand($container, $this->logger, ['--timeout' => 10, '--max-running-time' => 5]);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test] [127.0.0.1] Connected to host',
            'debug - [test] [127.0.0.1] Connected to host',
            'debug - [test] [127.0.0.1] Connected to host',
            'error - [master] Timeout waiting for clean shutdown, force kill',
            'info - [test] [127.0.0.1] [da:my-command/1/1] Child 1 1',
            'notice - [master] Stopping...',
            'notice - [test] [127.0.0.1] Ended',
            'notice - [test] [127.0.0.1] [master] Received SIGTERM, stopping',
            'notice - [test] [127.0.0.1] [master] Stopping...',
        ];
        $this->assertEquals($expected, $output);
    }

    private function executeCommand(ContainerInterface $container, LoggerInterface $logger, $input = [], $options = []): CommandTester
    {
        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $kernel->expects($this->any())->method('getContainer')->willReturn($container);
        $kernel->expects($this->any())->method('getBundles')->willReturn([]);

        /** @var KernelInterface */
        $kernelInterface = $kernel;

        $application = new Application($kernelInterface);
        $application->add(new MasterCommand($logger));

        $tester = new CommandTester($application->get('distributed_architecture:run-master'));
        $tester->execute($input, $options);

        return $tester;
    }

    private function buildContainer(string $config): ContainerInterface
    {
        $container = new ContainerBuilder();

        $config = Yaml::parse($config);

        $extension = new DistributedArchitectureExtension();
        $extension->load($config, $container);

        return $container;
    }

    private function buildBaseConfig(string $name, string $command, array $params = []): string
    {
        $paramsStr = '';
        if ($params) {
            $paramsStr = "            params:\n";
            foreach ($params as $key => $value) {
                $paramsStr .= "                {$key}: {$value}\n";
            }
        }

        return '
distributed_architecture:
    groups:
        '.$name.':
            command: '.$command.'
'.$paramsStr.'
';
    }

    private function buildLocalGroupConfig(string $name, string $command, $count = 1, array $params = []): ContainerInterface
    {
        $config = $this->buildBaseConfig($name, $command, $params);
        $config .= '            local:
                instances_count: '.$count.'
';

        return $this->buildContainer($config);
    }

    private function buildRemoteGroupConfig(string $name, string $command, $count = 1, array $params = []): ContainerInterface
    {
        $config = $this->buildBaseConfig($name, $command, $params);
        $config .= '            remote:
                -
                    instances_count: '.$count.'
                    hosts:
                        - 127.0.0.1
';

        return $this->buildContainer($config);
    }
}

class Logger extends AbstractLogger
{
    private $output = [];

    public function reset()
    {
        $this->output = [];
    }

    public function log($level, $message, array $context = [])
    {
        foreach ($context as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }
        $this->output[] = "{$level} - {$message}";
    }

    public function getOutput(): array
    {
        return $this->output;
    }
}
