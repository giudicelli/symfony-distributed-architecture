<?php

namespace giudicelli\DistributedArchitectureBundle;

use giudicelli\DistributedArchitecture\Master\Launcher as Launcher_;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Process as ProcessLocal;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Process as ProcessRemote;

/**
 * {@inheritdoc}
 *
 * @author Frédéric Giudicelli
 */
class Launcher extends Launcher_
{
    /**
     * {@inheritdoc}
     */
    protected function getProcessHandlersList(): array
    {
        return [
            ProcessLocal::class,
            ProcessRemote::class,
        ];
    }
}
