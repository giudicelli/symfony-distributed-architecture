<?php

namespace giudicelli\DistributedArchitectureBundle;

use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Slave\Handler as Handler_;
use giudicelli\DistributedArchitectureBundle\Event\EventsHandler;
use giudicelli\DistributedArchitectureBundle\Repository\ProcessStatusRepository;

class Handler extends Handler_
{
    /** @var ProcessStatusRepository */
    private $processStatusRepository;

    /**
     * @param string $params the JSON encoded params passed by the master process
     */
    public function __construct(string $params, ProcessStatusRepository $processStatusRepository = null)
    {
        parent::__construct($params);
    }

    protected function getCommandEventsObject(): ?EventsInterface
    {
        if (empty($this->params[self::PARAM_EVENTS_CLASS]) || !$this->processStatusRepository) {
            return null;
        }

        return new EventsHandler($this->processStatusRepository);
    }
}
