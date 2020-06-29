<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitecture\Config\GroupConfigInterface;
use giudicelli\DistributedArchitecture\Helper\InterProcessLogger;
use giudicelli\DistributedArchitecture\Slave\HandlerInterface;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Handler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A Symfony abstract slave command. It handles the launching of the Handler class.
 *
 * @author Frédéric Giudicelli
 */
abstract class AbstractSlaveCommand extends Command
{
    /** @var EventsHandler */
    private $eventsHandler;

    public function __construct(?EventsHandler $eventsHandler = null)
    {
        $this->eventsHandler = $eventsHandler;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($input->getOption('gda-params')) {
                $handler = new Handler($input->getOption('gda-params'), $this->eventsHandler);

                $me = $this;
                $handler->run(function (HandlerInterface $handler) use ($me) {
                    $me->runSlave($handler);
                });
            } else {
                $this->runSlave(null);
            }
        } catch (\Exception $e) {
            InterProcessLogger::sendLog('critical', $e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Commands can implement this method to have the master perform a pre run check.
     *
     * @param GroupConfigInterface $groupConfig the group config associated with this command
     * @param LoggerInterface $logger the logger
     * @return bool true if the command is valid else false
     */
    public function preRunCheck(GroupConfigInterface $groupConfig, LoggerInterface $logger): bool
    {
        return true;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('gda-params', null, InputOption::VALUE_REQUIRED, 'Internal params.');
    }

    /**
     * This method needs to be implemented, its purpose it the do the actual task the command is supposed to handle.
     *
     * @param null|HandlerInterface $handler null when the command wasn't started by the master, else an instance of the handler
     */
    abstract protected function runSlave(?HandlerInterface $handler): void;
}
