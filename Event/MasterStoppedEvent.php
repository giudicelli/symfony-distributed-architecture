<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;

/**
 * Dispatched when the a launcher is stopped.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStoppedEvent
{
    const NAME = 'distributed_architecture.master_stopped';

    private $launcher;

    public function __construct(LauncherInterface $launcher)
    {
        $this->launcher = $launcher;
    }

    public function getLauncher(): LauncherInterface
    {
        return $this->launcher;
    }
}
