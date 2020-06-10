<?php

namespace giudicelli\DistributedArchitectureBundle\Handler\Local\Consumer;

use giudicelli\DistributedArchitectureBundle\Handler\ProcessTrait;
use giudicelli\DistributedArchitectureQueue\Master\Handlers\Consumer\Local\Process as LocalProcess;

/**
 * A Symfony consumer process started on the local host.
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
