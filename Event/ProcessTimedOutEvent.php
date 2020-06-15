<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a process timed out.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessTimedOutEvent extends AbstractProcessEvent
{
    const NAME = 'distributed_architecture.process_timedout';
}
