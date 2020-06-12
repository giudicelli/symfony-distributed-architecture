<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;

/**
 * Dispatched when the a launcher has started.
 *
 * @author Frédéric Giudicelli
 */
final class MasterStartedEvent
{
    const NAME = 'distributed_architecture.master_started';

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
