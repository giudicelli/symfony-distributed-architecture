<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;

/**
 * Dispatched when a process is running, it will be dispatched multiple times.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessRunningEvent
{
    const NAME = 'distributed_architecture.process_running';

    private $process;
    private $line;

    public function __construct(ProcessInterface $process, string $line)
    {
        $this->process = $process;
        $this->line = $line;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    public function getLine(): string
    {
        return $this->line;
    }
}
