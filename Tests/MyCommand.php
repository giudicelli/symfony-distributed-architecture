<?php

namespace giudicelli\DistributedArchitectureBundle\Tests;

use giudicelli\DistributedArchitecture\Slave\HandlerInterface;
use giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveCommand;

/**
 * @author Frédéric Giudicelli
 */
class MyCommand extends AbstractSlaveCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('da:my-command');
        $this->setDescription('Launch the slave test command');
    }

    protected function runSlave(?HandlerInterface $handler): void
    {
        $groupConfig = $handler->getGroupConfig();

        $params = $groupConfig->getParams();

        if (!empty($params['message'])) {
            echo $params['message']."\n";
        } else {
            echo "Child {$handler->getId()} {$handler->getGroupId()} \n";
        }
        flush();

        if (!empty($params['sleep'])) {
            $handler->sleep($params['sleep']);
        } elseif (!empty($params['forceSleep'])) {
            sleep($params['forceSleep']);
        } elseif (!empty($params['neverDie'])) {
            for (;;) {
                sleep(1);
            }
        }

        echo "Child clean exit\n";
        flush();
    }
}
