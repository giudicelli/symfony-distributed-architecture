<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a launcher is stopped.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStoppedEvent extends AbstractMasterEvent
{
    const NAME = 'distributed_architecture.master_stopped';
}
