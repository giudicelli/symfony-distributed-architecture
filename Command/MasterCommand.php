<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitecture\Master\GroupConfigInterface;
use giudicelli\DistributedArchitecture\Master\Handlers\GroupConfig;
use giudicelli\DistributedArchitecture\Master\ProcessConfigInterface;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Config as ConfigLocal;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Config as ConfigRemote;
use giudicelli\DistributedArchitectureBundle\Launcher;
use giudicelli\DistributedArchitectureBundle\Repository\ProcessStatusRepository;
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
class MasterCommand extends Command
{
    protected static $defaultName = 'distributed_architecture:run-master';

    /**
     * @var null|ContainerInterface
     */
    private $container;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProcessStatusRepository */
    private $processStatusRepository;

    /** @var EventsHandler */
    private $eventsHandler;

    public function __construct(?ProcessStatusRepository $processStatusRepository = null, ?EventsHandler $eventsHandler = null)
    {
        $this->processStatusRepository = $processStatusRepository;
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
        if ($this->logger) {
            $logger = $this->logger;
        } else {
            $logger = new ConsoleLogger($output);
        }
        $launcher = new Launcher($logger);

        if ($input->getOption('timeout')) {
            $launcher->setTimeout($input->getOption('timeout'));
        }
        if ($input->getOption('max-process-timeout')) {
            $launcher->setMaxProcessTimeout($input->getOption('max-process-timeout'));
        }
        if ($input->getOption('max-running-time')) {
            $launcher->setMaxRunningTime($input->getOption('max-running-time'));
        }

        $groupConfigs = $this->getContainer()->getParameter('distributed_architecture.groups');
        if (!$groupConfigs) {
            return 0;
        }

        $config = $this->parseConfig($groupConfigs);

        $saveStates = $this->getContainer()->getParameter('distributed_architecture.save_states');
        if ($saveStates && $this->processStatusRepository && $this->eventsHandler) {
            $this->processStatusRepository->deleteAll();
            $events = $this->eventsHandler;
        } else {
            $events = null;
        }

        $launcher->run($config, $events);

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

        $this->addOption('max-running-time', null, InputOption::VALUE_OPTIONAL, 'Set the max running time for the master.');
        $this->addOption('max-process-timeout', null, InputOption::VALUE_OPTIONAL, 'Set the maximum number of times a process can timeout before it is considered dead and restarted. Default is 3.');
        $this->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Set the timeout for the master. Default is 300');
    }

    /**
     * Transform the groups configuration as it was parsed by Configuration into an array of GroupConfigInterface.
     *
     * @internal
     *
     * @return GroupConfigInterface[] The list of group configs
     */
    protected function parseConfig(array $groups): array
    {
        $groupConfigs = [];
        foreach ($groups as $name => $group) {
            $groupConfig = ['name' => $name];
            $processes = [];
            foreach ($group as $key => $value) {
                $key = $this->fixSnakeCase($key);
                switch ($key) {
                    case 'local':
                        $processes[] = $this->parseProcessConfig($value, ConfigLocal::class);

                    break;
                    case 'remote':
                        foreach ($value as $remote) {
                            $processes[] = $this->parseProcessConfig($remote, ConfigRemote::class);
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
