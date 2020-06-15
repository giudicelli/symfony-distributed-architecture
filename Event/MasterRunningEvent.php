<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

/**
 * Dispatched when the a launcher is running, it will be dispatched multiple times.
 *
 * @author Frédéric Giudicelli
 */
final class MasterRunningEvent extends AbstractMasterEvent
{
    const NAME = 'distributed_architecture.master_running';
}
