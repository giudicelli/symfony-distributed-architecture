<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a queue item was handled.
 *
 * @author Frédéric Giudicelli
 */
final class QueueItemHandledEvent extends AbstractQueueItemEvent
{
    const NAME = 'distributed_architecture.queue_item_handled';
}
