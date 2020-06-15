<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a process has started.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessStartedEvent extends AbstractProcessEvent
{
    const NAME = 'distributed_architecture.process_started';
}
