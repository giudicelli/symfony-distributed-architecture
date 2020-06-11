<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatched when the a launcher has started.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStartedEvent
{
    const NAME = 'distributed_architecture.master_started';

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
