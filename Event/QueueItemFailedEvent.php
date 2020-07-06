<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Slave\HandlerInterface;

/**
 * Dispatched when an error occured while handling a queue item.
 *
 * @author Frédéric Giudicelli
 */
final class QueueItemFailedEvent extends AbstractQueueItemEvent
{
    const NAME = 'distributed_architecture.queue_item_failed';

    private $throwable;

    public function __construct(HandlerInterface $handler, array $item, \Throwable $throwable)
    {
        $this->throwable = $throwable;

        parent::__construct($handler, $item);
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
