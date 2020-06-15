<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;

/**
 * Dispatched when a process is running, it will be dispatched multiple times.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessRunningEvent extends AbstractProcessEvent
{
    const NAME = 'distributed_architecture.process_running';

    private $line;

    public function __construct(ProcessInterface $process, string $line)
    {
        $this->line = $line;
        parent::__construct($process);
    }

    public function getLine(): string
    {
        return $this->line;
    }
}
