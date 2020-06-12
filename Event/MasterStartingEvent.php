<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;

/**
 * Dispatched when the a launcher is starting.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStartingEvent
{
    const NAME = 'distributed_architecture.master_starting';

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
