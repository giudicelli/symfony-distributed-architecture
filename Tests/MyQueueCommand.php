<?php

namespace giudicelli\DistributedArchitectureBundle\Tests;

use giudicelli\DistributedArchitecture\Slave\HandlerInterface;
use giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveQueueCommand;
use giudicelli\DistributedArchitectureQueue\Slave\Queue\Feeder\FeederInterface;

/**
 * @author Frédéric Giudicelli
 */
class MyQueueCommand extends AbstractSlaveQueueCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('da:my-queue-command');
        $this->setDescription('Launch the slave test queue command');
    }

    protected function getFeeder(): FeederInterface
    {
        return new Feeder();
    }

    protected function handleItem(HandlerInterface $handler, array $item): void
    {
        echo $item['type'].':'.$item['id']."\n";
    }
}

class Job implements \JsonSerializable
{
    public $id = 0;
    public $type = '';

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
        ];
    }
}

class Feeder implements FeederInterface
{
    private $items = [];
    private $successes = [];
    private $errors = [];

    public function __construct()
    {
        $item = new Job();
        $item->id = 1;
        $item->type = 'MyType';
        $this->items[] = $item;

        $item = new Job();
        $item->id = 2;
        $item->type = 'MyType';
        $this->items[] = $item;

        $item = new Job();
        $item->id = 3;
        $item->type = 'MyType';
        $this->items[] = $item;
    }

    public function empty(): bool
    {
        return empty($this->items);
    }

    public function get(): ?\JsonSerializable
    {
        if (empty($this->items)) {
            return null;
        }

        $item = $this->items[0];
        array_splice($this->items, 0, 1);

        return $item;
    }

    public function success(\JsonSerializable $item): void
    {
        $this->successes[] = $item;
    }

    public function error(\JsonSerializable $item): void
    {
        $this->errors[] = $item;
    }
}
