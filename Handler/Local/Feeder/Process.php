<?php

namespace giudicelli\DistributedArchitectureBundle\Handler\Local\Feeder;

use giudicelli\DistributedArchitectureBundle\Handler\ProcessTrait;
use giudicelli\DistributedArchitectureQueue\Master\Handlers\Feeder\Local\Process as LocalProcess;

/**
 * A Symfony feeder process started on the local host.
 *
 * @author Frédéric Giudicelli
 *
 * @internal
 */
class Process extends LocalProcess
{
    use ProcessTrait;

    /**
     * {@inheritdoc}
     */
    public static function getConfigClass(): string
    {
        return Config::class;
    }
}
