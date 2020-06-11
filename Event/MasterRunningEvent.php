<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatched when the a launcher is running, it will be dispatched multiple times.
 *
 * @author Frédéric Giudicelli
 */
final class MasterRunningEvent
{
    const NAME = 'distributed_architecture.master_running';

    private $launcher;
    private $logger;

    public function __construct(LauncherInterface $launcher, LoggerInterface $logger)
    {
        $this->launcher = $launcher;
        $this->logger = $logger;
    }

    public function getLauncher(): LauncherInterface
    {
        return $this->launcher;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
