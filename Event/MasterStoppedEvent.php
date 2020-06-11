<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatched when the a launcher is stopped.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStoppedEvent
{
    const NAME = 'distributed_architecture.master_stopped';

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
