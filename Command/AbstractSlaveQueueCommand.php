<?php

namespace giudicelli\DistributedArchitectureBundle\Command;

use giudicelli\DistributedArchitecture\Config\GroupConfigInterface;
use giudicelli\DistributedArchitecture\Helper\InterProcessLogger;
use giudicelli\DistributedArchitecture\Slave\HandlerInterface;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Event\QueueItemFailedEvent;
use giudicelli\DistributedArchitectureBundle\Event\QueueItemHandledEvent;
use giudicelli\DistributedArchitectureBundle\Event\QueueItemReceivedEvent;
use giudicelli\DistributedArchitectureBundle\HandlerQueue;
use giudicelli\DistributedArchitectureBundle\Logger\LoggerDecorator;
use giudicelli\DistributedArchitectureQueue\Slave\Queue\Feeder\FeederInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A Symfony abstract slave queue command. It handles the launching of the Handler class.
 *
 * @author Frédéric Giudicelli
 */
abstract class AbstractSlaveQueueCommand extends Command
{
    /** @var null|EventsHandler */
    protected $eventsHandler;

    /** @var null|ParameterBagInterface */
    protected $parameters;

    /** @var null|EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        EventsHandler $eventsHandler = null,
        ParameterBagInterface $parameters = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->eventsHandler = $eventsHandler;
        $this->parameters = $parameters;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        LoggerDecorator::configure(true, true);

        try {
            $handler = new HandlerQueue(
                $input->getOption('gda-params'),
                $this->eventsHandler,
                $this->parameters
            );

            $me = $this;
            $handler->runQueue(function (HandlerInterface $handler, array $item) use ($me) {
                $me->doHandleItem($handler, $item);
            }, $this->getFeeder());
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
     * @param LoggerInterface      $logger      the logger
     *
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

    private function doHandleItem(HandlerInterface $handler, array $item): void
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new QueueItemReceivedEvent($handler, $item), QueueItemReceivedEvent::NAME);
        }

        try {
            $this->handleItem($handler, $item);
            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new QueueItemHandledEvent($handler, $item), QueueItemHandledEvent::NAME);
            }
        } catch (\Throwable $throwable) {
            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new QueueItemFailedEvent($handler, $item, $throwable), QueueItemFailedEvent::NAME);
            }
            InterProcessLogger::sendLog('error', $throwable->getMessage());
        }
    }
}
