<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitectureBundle\Launcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MasterCommand extends Command
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var Launcher */
    protected $launcher;

    /**
     * @var null|ContainerInterface
     */
    private $container;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->launcher = new Launcher($logger);
        parent::__construct();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('timeout')) {
            $this->launcher->setTimeout($input->getOption('timeout'));
        }
        if ($input->getOption('max-process-timeout')) {
            $this->launcher->setMaxProcessTimeout($input->getOption('max-process-timeout'));
        }
        if ($input->getOption('max-running-time')) {
            $this->launcher->setMaxRunningTime($input->getOption('max-running-time'));
        }

        /** @var array<GroupConfigInterface> */
        $groupConfigs = $this->getContainer()->getParameter('distributed_architecture.groups');

        if (!$groupConfigs) {
            return 0;
        }

        $this->launcher->run($groupConfigs);

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
        $this->setName('distributed_architecture:run-master');
        $this->setDescription('Launch all configured processes');

        $this->addOption('max-running-time', null, InputOption::VALUE_OPTIONAL, 'Set the max running time for the master.');
        $this->addOption('max-process-timeout', null, InputOption::VALUE_OPTIONAL, 'Set the maximum number of times a process can timeout before it is considered dead and restarted. Default is 3.');
        $this->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Set the timeout for the master. Default is 300');
    }
}
