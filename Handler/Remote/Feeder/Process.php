<?php

namespace giudicelli\DistributedArchitectureBundle\Handler\Remote\Feeder;

use giudicelli\DistributedArchitectureBundle\Handler\Local\Feeder\Config as LocalConfig;
use giudicelli\DistributedArchitectureBundle\Handler\ProcessTrait;
use giudicelli\DistributedArchitectureQueue\Master\Handlers\Feeder\Remote\Process as RemoteProcess;

/**
 * A Symfony feeder process started on a remote host.
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
