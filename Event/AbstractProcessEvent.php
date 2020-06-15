<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\ProcessInterface;

/**
 * Process related event.
 *
 * @author Frédéric Giudicelli
 */
abstract class AbstractProcessEvent
{
    protected $process;

    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }
}
