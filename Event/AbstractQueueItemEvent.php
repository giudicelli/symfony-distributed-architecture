<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Slave\HandlerInterface;

/**
 * Abstract queue item event.
 *
 * @author Frédéric Giudicelli
 */
abstract class AbstractQueueItemEvent
{
    private $handler;
    private $item;

    public function __construct(HandlerInterface $handler, array $item)
    {
        $this->handler = $handler;
        $this->item = $item;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function getHandler(): HandlerInterface
    {
        return $this->handler;
    }
}
