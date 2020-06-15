<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when a launcher is starting.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStartingEvent extends AbstractMasterEvent
{
    const NAME = 'distributed_architecture.master_starting';
}
