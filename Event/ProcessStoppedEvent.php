<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a process has stopped.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessStoppedEvent extends AbstractProcessEvent
{
    const NAME = 'distributed_architecture.process_stopped';
}
