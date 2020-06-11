<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatched when a process has started.
 *
 * @author Frédéric Giudicelli
 */
final class ProcessStartedEvent
{
    const NAME = 'distributed_architecture.process_started';

    private $process;
    private $logger;

    public function __construct(ProcessInterface $process, LoggerInterface $logger)
    {
        $this->process = $process;
        $this->logger = $logger;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
