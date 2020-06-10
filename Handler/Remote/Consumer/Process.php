<?php

namespace giudicelli\DistributedArchitectureBundle\Handler\Remote\Consumer;

use giudicelli\DistributedArchitectureBundle\Handler\Local\Consumer\Config as LocalConfig;
use giudicelli\DistributedArchitectureBundle\Handler\ProcessTrait;
use giudicelli\DistributedArchitectureQueue\Master\Handlers\Consumer\Remote\Process as RemoteProcess;

/**
 * A Symfony consumer process started on a remote host.
 *
 * @author Frédéric Giudicelli
 *
 * @internal
 */
class Process extends RemoteProcess
{
    use ProcessTrait;

    /**
     * {@inheritdoc}
     */
    public static function getConfigClass(): string
    {
        return Config::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRemoteConfigClass(): string
    {
        return LocalConfig::class;
    }
}
