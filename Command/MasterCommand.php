<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitecture\Config\GroupConfig;
use giudicelli\DistributedArchitecture\Config\GroupConfigInterface;
use giudicelli\DistributedArchitecture\Config\ProcessConfigInterface;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Config as LocalConfig;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Consumer\Config as LocalConsumerConfig;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Feeder\Config as LocalFeederConfig;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Config as RemoteConfig;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Consumer\Config as RemoteConsumerConfig;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Feeder\Config as RemoteFeederConfig;
use giudicelli\DistributedArchitectureBundle\Launcher;
use giudicelli\DistributedArchitectureBundle\Logger\LoggerDecorator;
use giudicelli\DistributedArchitectureBundle\Logger\ServiceLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This is the main command to launch the distributed architecture. It will parse the configuration and start all the slave processes.
 *
 * @author Frédéric Giudicelli
 */
class MasterCommand extends Command implements LoggerAwareInterface
{
    protected static $defaultName = 'distributed_architecture:run-master';

    /**
     * @var null|ContainerInterface
     */
    private $container;

    /** @var LoggerInterface */
    private $logger;

    /** @var EventsHandler */
    private $eventsHandler;

    public function __construct(?EventsHandler $eventsHandler = null)
    {
        $this->eventsHandler = $eventsHandler;

        parent::__construct();
    }

    /**
     * Used for the tests.
     *
     * @internal
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        LoggerDecorator::configure(true, false);

        if ($this->logger) {
            $this->logger = new ServiceLogger($this->logger);
        } else {
            $this->logger = new ServiceLogger(new ConsoleLogger($output));
        }

        // Parse the config
        $groupConfigs = $this->buildGroupConfigs();
        if (!$groupConfigs) {
            return 0;
        }

        // Will fork a detached service
        if ($input->getOption('service')) {
            $groupConfigs = $this->checkGroupConfigs($groupConfigs);
            if (!$groupConfigs) {
                return 0;
            }

            return $this->startAsService($input);
        }

        $runAsService = !empty(getenv('RUN_AS_SERVICE'));
        if (!$runAsService) {
            $groupConfigs = $this->checkGroupConfigs($groupConfigs);
            if (!$groupConfigs) {
                return 0;
            }
        }

        $launcher = new Launcher(true, $this->logger);
        if ($input->getOption('timeout')) {
            $launcher->setTimeout($input->getOption('timeout'));
        }
        if ($input->getOption('max-process-timeout')) {
            $launcher->setMaxProcessTimeout($input->getOption('max-process-timeout'));
        }
        if ($input->getOption('max-running-time')) {
            $launcher->setMaxRunningTime($input->getOption('max-running-time'));
        }

        $launcher
            ->setGroupConfigs($groupConfigs)
            ->setEventsHandler($this->eventsHandler)
            ->runMaster($runAsService)
        ;

        if ($input->getOption('pid')) {
            @unlink($input->getOption('pid'));
        }

        return 0;
    }

    /**
     * Used for the tests.
     *
     * @internal
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Forks a detached instance of this command.
     *
     * @param InputInterface $input The input
     *
     * @return int The return code
     */
    protected function startAsService(InputInterface $input): int
    {
        $runas_uid = null;
        $runas_gid = null;

        $is_root = (0 == posix_getuid());

        if ($is_root) {
            if ($input->getOption('group')) {
                $group = posix_getgrnam($input->getOption('group'));
                if (!$group) {
                    $this->logger->critical('Group '.$input->getOption('group').' is unknown');

                    return 1;
                }
                $runas_gid = $group['gid'];
            }

            if (!$input->getOption('user')) {
                $this->logger->critical("You're running as root, you must specify a user");

                return 1;
            }

            $user = posix_getpwnam($input->getOption('user'));
            if (!$user) {
                $this->logger->critical('User '.$input->getOption('user').' is unknown');

                return 1;
            }
            $runas_uid = $user['uid'];
            if (!$runas_gid) {
                $runas_gid = $user['gid'];
            }
        }

        switch (($pid = pcntl_fork())) {
            case -1:
                $this->logger->critical('Failed to fork');

                return 1;
            case 0:
                // Child
                $pid = posix_getpid();
                // We can now switch to the runas user
                if ($runas_uid) {
                    posix_setgid($runas_uid);
                }
                if ($runas_gid) {
                    posix_setuid($runas_gid);
                }
                posix_setsid();

                if ($input->getOption('pid')) {
                    if (!file_put_contents($input->getOption('pid'), $pid)) {
                        $this->logger->critical('Failed to write to '.$input->getOption('pid'));

                        return 1;
                    }
                }

                if ($input->getOption('log')) {
                    $fdout = fopen($input->getOption('log'), 'ab');
                    if (!$fdout) {
                        $this->logger->critical('Failed to open '.$input->getOption('log'));

                        return 1;
                    }
                    \eio_dup2($fdout, STDOUT);
                    \eio_dup2($fdout, STDERR);
                    \eio_event_loop();
                    fclose($fdout);
                }

                $args = [];
                foreach ($_SERVER['argv'] as $arg) {
                    if ('--service' === $arg) {
                        continue;
                    }
                    $args[] = $arg;
                }

                $envs = [
                    'RUN_AS_SERVICE' => 1,
                ];

                pcntl_exec(PHP_BINARY, $args, $envs);

                break;
            default:
                // parent
                sleep(2);
                $status = 0;
                if (0 != pcntl_waitpid($pid, $status, WNOHANG)) {
                    $this->logger->critical('Forked service exited abnormally.');

                    return 1;
                }
        }

        return 0;
    }

    /**
     * @throws \LogicException
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            /** @var mixed */
            $application = $this->getApplication();
            if (null === $application) {
                throw new \LogicException('The container cannot be retrieved as the application instance is not yet set.');
            }

            $this->container = $application->getKernel()->getContainer();
        }

        return $this->container;
    }

    protected function configure()
    {
        $this->setDescription('Launch all configured processes');

        $this->addOption('max-running-time', null, InputOption::VALUE_REQUIRED, 'Set the max running time for the master.');
        $this->addOption('max-process-timeout', null, InputOption::VALUE_REQUIRED, 'Set the maximum number of times a process can timeout before it is considered dead and restarted. Default is 3.');
        $this->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'Set the timeout for the master. Default is 300');
        $this->addOption('service', null, InputOption::VALUE_NONE, 'Run as a service, do not exit when all processes are done, wait for commands');
        $this->addOption('user', null, InputOption::VALUE_REQUIRED, 'When --service is activated, run as this user. Ignored if not root.');
        $this->addOption('group', null, InputOption::VALUE_REQUIRED, 'When --service is activated, run as this group. Ignored if not root.');
        $this->addOption('log', null, InputOption::VALUE_REQUIRED, 'When --service is activated, specify in which file to log.');
        $this->addOption('pid', null, InputOption::VALUE_REQUIRED, 'When --service is activated, specify in which file to store the PID of the service.');
    }

    /**
     * Transform the parsed group configs into instances of GroupConfigInterface.
     *
     * @internal
     *
     * @return GroupConfigInterface[] The list of group configs
     */
    protected function buildGroupConfigs(): array
    {
        /** @var array<GroupConfigInterface> */
        $allGroupConfigs = [];
        $groupConfigs = $this->getContainer()->getParameter('distributed_architecture.groups');
        if ($groupConfigs) {
            $allGroupConfigs = $this->parseGroupConfigs($groupConfigs);
        }

        $groupConfigs = $this->getContainer()->getParameter('distributed_architecture.queue_groups');
        if ($groupConfigs) {
            $queueConfig = $this->parseQueueConfig($groupConfigs);
            // Make sure group names are unique
            if ($allGroupConfigs) {
                foreach ($queueConfig as $queueGroupConfig) {
                    foreach ($allGroupConfigs as $groupConfig) {
                        if ($queueGroupConfig->getName() === $groupConfig->getName()) {
                            $this->logger->critical('Duplicate group name for '.$queueGroupConfig->getName());

                            return [];
                        }
                    }
                }
            }
            $allGroupConfigs = array_merge($allGroupConfigs, $queueConfig);
        }

        return $allGroupConfigs;
    }

    /**
     * Check the array of GroupConfigInterface.
     *
     * @param GroupConfigInterface[] $configs the configs to check
     *
     * @internal
     *
     * @return GroupConfigInterface[] the valid group configs that are runnable
     */
    protected function checkGroupConfigs(array $groupConfigs): array
    {
        $validGroupConfigs = [];
        foreach ($groupConfigs as $groupConfig) {
            if ($this->checkGroupCommand($groupConfig)) {
                $validGroupConfigs[] = $groupConfig;
            }
        }

        return $validGroupConfigs;
    }

    /**
     * Perform a check on a group. If the group's command is locally known and is an instance of AbstractSlaveCommand or AbstractSlaveQueueCommand, it calls the command's preRunCheck method, which allows the command to run some checks, once, before all its instances are run.
     *
     * @internal
     *
     * @param GroupConfigInterface $groupConfig the group config to check
     *
     * @return bool true if the command is valid, else false
     */
    protected function checkGroupCommand(GroupConfigInterface $groupConfig): bool
    {
        // If the command is executed in the same environment as the master's
        // we can perform a few checks

        $commandStr = explode(' ', $groupConfig->getCommand())[0];
        $app = $this->getApplication();

        // This environment knows nothing about this command
        if (!$app->has($commandStr)) {
            return true;
        }
        $command = $app->get($commandStr);

        if (!($command instanceof AbstractSlaveCommand)
        && !($command instanceof AbstractSlaveQueueCommand)) {
            return true;
        }

        return $command->preRunCheck($groupConfig, $this->logger);
    }

    /**
     * Transform the groups configuration as it was parsed by Configuration into an array of GroupConfigInterface.
     *
     * @internal
     *
     * @return GroupConfigInterface[] The list of group configs
     */
    protected function parseGroupConfigs(array $groups): array
    {
        $groupConfigs = [];
        foreach ($groups as $name => $group) {
            $groupConfig = ['name' => $name];
            $processes = [];
            foreach ($group as $key => $value) {
                $key = $this->fixSnakeCase($key);
                switch ($key) {
                    case 'local':
                        $processes[] = $this->parseProcessConfig($value, LocalConfig::class);

                    break;
                    case 'remote':
                        foreach ($value as $remote) {
                            $processes[] = $this->parseProcessConfig($remote, RemoteConfig::class);
                        }

                    break;
                    default:
                        $groupConfig[$key] = $value;

                    break;
                }
            }
            $groupConfigObject = new GroupConfig();
            $groupConfigObject->fromArray($groupConfig);
            $groupConfigObject->setProcessConfigs($processes);
            $groupConfigs[] = $groupConfigObject;
        }

        return $groupConfigs;
    }

    /**
     * Transform the groups configuration as it was parsed by Configuration into an array of GroupConfigInterface.
     *
     * @internal
     *
     * @return GroupConfigInterface[] The list of group configs
     */
    protected function parseQueueConfig(array $groups): array
    {
        $groupConfigs = [];
        foreach ($groups as $name => $group) {
            $groupConfig = ['name' => $name];
            $processes = [];
            foreach ($group as $key => $value) {
                $key = $this->fixSnakeCase($key);
                switch ($key) {
                    case 'localFeeder':
                        $processes[] = $this->parseProcessConfig($value, LocalFeederConfig::class);

                    break;
                    case 'remoteFeeder':
                        $processes[] = $this->parseProcessConfig($value, RemoteFeederConfig::class);

                    break;
                    case 'consumers':
                        foreach ($value as $consumerType => $consumer) {
                            switch ($consumerType) {
                                case 'local':
                                    $processes[] = $this->parseProcessConfig($consumer, LocalConsumerConfig::class);

                                break;
                                case 'remote':
                                    foreach ($consumer as $remote) {
                                        $processes[] = $this->parseProcessConfig($remote, RemoteConsumerConfig::class);
                                    }

                                break;
                            }
                        }

                    break;
                    default:
                        $groupConfig[$key] = $value;

                    break;
                }
            }
            $groupConfigObject = new GroupConfig();
            $groupConfigObject->fromArray($groupConfig);
            $groupConfigObject->setProcessConfigs($processes);
            $groupConfigs[] = $groupConfigObject;
        }

        return $groupConfigs;
    }

    /**
     * Transform the process configuration as it was parsed by Configuration into a ProcessConfigInterface.
     *
     * @internal
     *
     * @return ProcessConfigInterface The process config
     */
    protected function parseProcessConfig(array $config, string $class): ProcessConfigInterface
    {
        $processConfig = [];
        foreach ($config as $key => $value) {
            $processConfig[$this->fixSnakeCase($key)] = $value;
        }
        $processConfigObject = new $class();
        $processConfigObject->fromArray($processConfig);

        return $processConfigObject;
    }

    /**
     * Transform a string from Snake Case to Camel Case.
     *
     * @internal
     *
     * @param string $value the string to convert
     *
     * @return string The converted string
     */
    protected function fixSnakeCase(string $value): string
    {
        $parts = explode('_', $value);
        if (1 === count($parts)) {
            return $value;
        }
        for ($i = 1; $i < count($parts); ++$i) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return join('', $parts);
    }
}
