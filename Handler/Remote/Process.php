<?php

namespace giudicelli\DistributedArchitectureBundle\Handler\Remote;

use giudicelli\DistributedArchitecture\Master\Handlers\Remote\Process as RemoteProcess;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Config as ConfigLocal;
use giudicelli\DistributedArchitectureBundle\Handler\ProcessTrait;
use giudicelli\DistributedArchitectureBundle\Launcher;

/**
 * A symnfony consumer process started on a remote host.
 *
 * @author Frédéric Giudicelli
 *
 * @internal
 */
class Process extends RemoteProcess
{
    use ProcessTrait;

    public static function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getRemoteConfigClass(): string
    {
        return ConfigLocal::class;
    }

    protected function getRemoteLauncherClass(): string
    {
        return Launcher::class;
    }
}
