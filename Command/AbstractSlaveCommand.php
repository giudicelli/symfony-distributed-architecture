<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitectureBundle\Handler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSlaveCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $handler = new Handler($input->getOption('params'));

        $me = $this;
        $handler->run(function (Handler $handler) use ($me) {
            $me->runSlave($handler);
        });

        return 0;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('params', null, InputOption::VALUE_REQUIRED, 'Internal params.');
    }

    abstract protected function runSlave(Handler $handler): void;
}
