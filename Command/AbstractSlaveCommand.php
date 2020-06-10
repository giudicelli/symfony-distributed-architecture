<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

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
        if ($input->getOption('gda-params')) {
            $handler = new Handler($input->getOption('gda-params'), $this->eventsHandler);

            $me = $this;
            $handler->run(function (Handler $handler, LoggerInterface $logger) use ($me) {
                $me->runSlave($handler, $logger);
            });
        } else {
            $this->runSlave(null, null);
        }

        return 0;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('gda-params', null, InputOption::VALUE_REQUIRED, 'Internal params.');
    }

    /**
     * This method need to be implemented, its purpose it the do the actual task the command is supposed to handle.
     *
     * @param null|Handler         $handler null when the command wasn't started by the master, else an instance of the handler
     * @param null|LoggerInterface $logger  null when the command wasn't started by the master, else a LoggerInterface to allow logs to be properly passed back to the master command
     */
    abstract protected function runSlave(?Handler $handler, ?LoggerInterface $logger): void;
}
