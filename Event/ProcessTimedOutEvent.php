<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;

/**
 * Dispatched when a process timed out.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessTimedOutEvent
{
    const NAME = 'distributed_architecture.process_timedout';

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
