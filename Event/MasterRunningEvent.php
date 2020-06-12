<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;

/**
 * Dispatched when the a launcher is running, it will be dispatched multiple times.
 *
 * @author Frédéric Giudicelli
 */
final class MasterRunningEvent
{
    const NAME = 'distributed_architecture.master_running';

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
