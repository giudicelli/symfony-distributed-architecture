<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when the a launcher has started.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStartedEvent extends AbstractMasterEvent
{
    const NAME = 'distributed_architecture.master_started';
}
