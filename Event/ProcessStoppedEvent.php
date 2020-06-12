<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;

/**
 * Dispatched when a process has stopped.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessStoppedEvent
{
    const NAME = 'distributed_architecture.process_stopped';

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
