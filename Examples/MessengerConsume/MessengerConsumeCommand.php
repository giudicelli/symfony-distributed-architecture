<?php

namespace App\Command;

use giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveCommand;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Handler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Worker;

/**
 * This example provides a compatible distributed-architecture implementation of Symfony's messenger:consume command.
 *
 * Example of a configuration:
 * ----
 * distributed_architecture:
 *   groups:
 *     Messenger Consume:
 *       command: app:messenger-consume
 *       params:
 *         receivers: async, other_transport # Or whatever transports you configured in messenger.yaml
 *       local:
 *         instances_count: 2
 *       remote:
 *         -
 *           instances_count: 2
 *           hosts:
 *             - server1
 *             - server2
 * ----
 *
 * In services.yaml, you need to configure the constructor's parameters, by adding the following entry:
 * ----
 * services:
 *   App\Command\MessengerConsumeCommand:
 *      arguments: ['@giudicelli\DistributedArchitectureBundle\Event\EventsHandler', '@messenger.routable_message_bus', '@messenger.receiver_locator', '@event_dispatcher']
 * ----
 *
 * @author Frédéric Giudicelli
 */
class MessengerConsumeCommand extends AbstractSlaveCommand
{
    private $routableBus;
    private $receiverLocator;
    private $eventDispatcher;

    public function __construct(
        EventsHandler $eventsHandler,
        RoutableMessageBus $routableBus,
        ContainerInterface $receiverLocator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->routableBus = $routableBus;
        $this->receiverLocator = $receiverLocator;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct($eventsHandler);
    }

    protected function configure()
    {
        parent::configure();
        $this->setName('app:messenger-consume');
        $this->setDescription('Symfony messenger consume');
    }

    protected function runSlave(?Handler $handler, ?LoggerInterface $logger): void
    {
        if (!$handler) {
            echo "This command cannot be directly executed\n";

            return;
        }

        $groupConfig = $handler->getGroupConfig();

        $params = $groupConfig->getParams();
        if (empty($params['receivers'])) {
            $logger->critical('There should be a receivers param');

            return;
        }

        $receivers = [];
        foreach (explode(',', $params['receivers']) as $receiverName) {
            $receiverName = trim($receiverName);
            if (!$this->receiverLocator->has($receiverName)) {
                $logger->critical('The receiver "{receiver}" does not exist.', ['receiver' => $receiverName]);

                return;
            }

            $receivers[$receiverName] = $this->receiverLocator->get($receiverName);
        }

        $this->eventDispatcher->addListener(WorkerRunningEvent::class, function (WorkerRunningEvent $event) use ($handler) {
            if ($handler->mustStop()) {
                $event->getWorker()->stop();
            }
            if ($event->isWorkerIdle()) {
                $handler->ping();
            }
        });

        $logger->info('Waiting to consume messages from {receivers}', ['receivers' => $params['receivers']]);

        $worker = new Worker($receivers, $this->routableBus, $this->eventDispatcher, $logger);
        $worker->run([
            'sleep' => 2000000, //2s
        ]);
    }
}
