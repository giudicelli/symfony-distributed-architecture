<?php

namespace giudicelli\DistributedArchitectureBundle;

use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Slave\Handler as Handler_;

class Handler extends Handler_
{
    /** @var EventsInterface */
    private $eventsHandler;

    /**
     * @param string          $params        the JSON encoded params passed by the master process
     * @param EventsInterface $eventsHandler the optional events handler
     */
    public function __construct(string $params, EventsInterface $eventsHandler = null)
    {
        $this->eventsHandler = $eventsHandler;
        parent::__construct($params);
    }

    protected function getCommandEventsObject(): ?EventsInterface
    {
        if (empty($this->params[self::PARAM_EVENTS_CLASS]) || !$this->eventsHandler) {
            return null;
        }

        return $this->eventsHandler;
    }
}
