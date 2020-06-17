<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitecture\Helper\InterProcessLogger;
use giudicelli\DistributedArchitecture\Slave\HandlerInterface;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\HandlerQueue;
use giudicelli\DistributedArchitectureQueue\Slave\Queue\Feeder\FeederInterface;
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
        try {
            $handler = new HandlerQueue($input->getOption('gda-params'), $this->eventsHandler);

            $me = $this;
            $handler->runQueue(function (HandlerInterface $handler, array $item) use ($me) {
                $me->handleItem($handler, $item);
            }, $this->getFeeder());
        } catch (\Exception $e) {
            InterProcessLogger::sendLog('critical', $e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('gda-params', null, InputOption::VALUE_REQUIRED, 'Internal params.');
    }

    /**
     * This method needs to be implemented, it returns the instance of the feeder.
     *
     * @return FeederInterface the feeder instance
     */
    abstract protected function getFeeder(): FeederInterface;

    /**
     * This method needs to be implemented, its purpose is to handle each item passed by the feeder.
     *
     * @param HandlerInterface $handler the instance of the handler
     * @param array            $item    the item to handle
     */
    abstract protected function handleItem(HandlerInterface $handler, array $item): void;
}
