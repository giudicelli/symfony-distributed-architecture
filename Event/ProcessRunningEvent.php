<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatched when a process is running, it will be dispatched multiple times.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessRunningEvent
{
    const NAME = 'distributed_architecture.process_running';

    private $process;
    private $logger;
    private $line;

    public function __construct(ProcessInterface $process, string $line, LoggerInterface $logger)
    {
        $this->process = $process;
        $this->line = $line;
        $this->logger = $logger;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
