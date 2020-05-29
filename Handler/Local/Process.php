<?php

namespace giudicelli\DistributedArchitectureBundle\Handler\Local;

use giudicelli\DistributedArchitecture\Master\Handlers\Local\Process as LocalProcess;
use giudicelli\DistributedArchitectureBundle\Handler\ProcessTrait;

/**
 * A symfony process started on the same computer as the master.
 *
 * @author Frédéric Giudicelli
 *
 * @internal
 */
class Process extends LocalProcess
{
    use ProcessTrait;

    public static function getConfigClass(): string
    {
        return Config::class;
    }
}
