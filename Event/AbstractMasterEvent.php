<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;

/**
 * Master related event.
 *
 * @author Frédéric Giudicelli
 */
abstract class AbstractMasterEvent
{
    protected $launcher;

    public function __construct(LauncherInterface $launcher)
    {
        $this->launcher = $launcher;
    }

    public function getLauncher(): LauncherInterface
    {
        return $this->launcher;
    }
}
