<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;

/**
 * Dispatched when a process has started.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessStartedEvent
{
    const NAME = 'distributed_architecture.process_started';

    private $process;

    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }
}
