<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a process was created.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessCreatedEvent extends AbstractProcessEvent
{
    const NAME = 'distributed_architecture.process_created';
}
