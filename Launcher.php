<?php

namespace giudicelli\DistributedArchitectureBundle;

use giudicelli\DistributedArchitecture\Master\Launcher as Launcher_;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Process as ProcessLocal;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Process as ProcessRemote;

class Launcher extends Launcher_
{
    protected function getProcessHandlersList(): array
    {
        return [
            ProcessLocal::class,
            ProcessRemote::class,
        ];
    }
}
