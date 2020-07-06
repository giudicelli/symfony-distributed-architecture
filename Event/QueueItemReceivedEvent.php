<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a queue item is received.
 *
 * @author Frédéric Giudicelli
 */
final class QueueItemReceivedEvent extends AbstractQueueItemEvent
{
    const NAME = 'distributed_architecture.queue_item_received';
}
