<?php

namespace giudicelli\DistributedArchitectureBundle;

use giudicelli\DistributedArchitecture\Config\ProcessConfigInterface;
use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Slave\Handler as Handler_;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * {@inheritdoc}
 *
 * @author Frédéric Giudicelli
 */
class Handler extends Handler_
{
    /** @var null|EventsInterface */
    private $eventsHandler;

    /** @var null|ParameterBagInterface */
    private $parameters;

    /**
     * @param string                     $params        the JSON encoded params passed by the master process
     * @param null|EventsInterface       $eventsHandler the optional events handler
     * @param null|ParameterBagInterface $parameters    the optional parameter bag
     */
    public function __construct(
        string $params,
        EventsInterface $eventsHandler = null,
        ParameterBagInterface $parameters = null
    ) {
        $this->eventsHandler = $eventsHandler;
        $this->parameters = $parameters;
        parent::__construct($params);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandEventsObject(): ?EventsInterface
    {
        if (empty($this->params[self::PARAM_EVENTS_CLASS]) || !$this->eventsHandler) {
            return null;
        }

        return $this->eventsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPidFileFromConfig(ProcessConfigInterface $config): string
    {
        if (!$this->parameters) {
            return parent::getPidFileFromConfig($config);
        }

        $uniqueId = sha1($this->id.'-'.$this->groupId.'-'.$this->groupConfig->getHash().'-'.$config->getHash());

        return $this->parameters->get('kernel.logs_dir').DIRECTORY_SEPARATOR.'gda-'.$uniqueId.'.pid';
    }
}
