<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\HandlerQueue;
use giudicelli\DistributedArchitectureQueue\Slave\Queue\Feeder\FeederInterface;
use giudicelli\DistributedArchitectureQueue\tests\Feeder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A Symfony abstract slave queue command. It handles the launching of the Handler class.
 *
 * @author Frédéric Giudicelli
 */
abstract class AbstractSlaveQueueCommand extends Command
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
        if (!$input->getOption('gda-params')) {
            $output->writeln('Empty gda-params');

            return 1;
        }

        $handler = new HandlerQueue($input->getOption('gda-params'), $this->eventsHandler);

        $me = $this;
        $handler->runQueue(function (HandlerQueue $handler, array $item, LoggerInterface $logger) use ($me) {
            $me->handleItem($handler, $item, $logger);
        }, $this->getFeeder());

        return 0;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('gda-params', null, InputOption::VALUE_REQUIRED, 'Internal params.');
    }

    /**
     * This method need to be implemented, it returns the instance of the feeder.
     *
     * @return FeederInterface the feeder instance
     */
    abstract protected function getFeeder(): FeederInterface;

    /**
     * This method need to be implemented, its purpose is to handle each item passed by the feeder.
     *
     * @param HandlerQueue    $handler the instance of the handler
     * @param array           $item    the item to handle
     * @param LoggerInterface $logger  a LoggerInterface to allow logs to be properly passed back to the master command
     */
    abstract protected function handleItem(HandlerQueue $handler, array $item, LoggerInterface $logger): void;
}
