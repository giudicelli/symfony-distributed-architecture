<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Handler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            $handler->run(function (Handler $handler) use ($me) {
                $me->runSlave($handler);
            });
        } else {
            $this->runSlave(null);
        }

        return 0;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('gda-params', null, InputOption::VALUE_OPTIONAL, 'Internal params.');
    }

    abstract protected function runSlave(?Handler $handler): void;
}
