<?php

namespace giudicelli\DistributedArchitectureBundle;

use giudicelli\DistributedArchitecture\Config\GroupConfigInterface;
use giudicelli\DistributedArchitecture\Master\Launcher as _Launcher;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Consumer\Process as LocalConsumerProcess;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Feeder\Process as LocalFeederProcess;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Process as LocalProcess;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Consumer\Process as RemoteConsumerProcess;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Feeder\Process as RemoteFeederProcess;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Process as RemoteProcess;
use giudicelli\DistributedArchitectureQueue\Master\Handlers\Consumer\ConfigInterface as ConsumerConfigInterface;
use giudicelli\DistributedArchitectureQueue\Master\Handlers\Feeder\ConfigInterface as FeederConfigInterface;
use giudicelli\DistributedArchitectureQueue\Master\LauncherQueue;

/**
 * {@inheritdoc}
 *
 * @author Frédéric Giudicelli
 */
class Launcher extends LauncherQueue
{
    /**
     * {@inheritdoc}
     */
    protected function checkGroupConfigs(array $groupConfigs): void
    {
        $normalGroups = [];
        $queueGroups = [];

        foreach ($groupConfigs as $groupConfig) {
            if ($this->isQueueGroup($groupConfig)) {
                $queueGroups[] = $groupConfig;
            } else {
                $normalGroups[] = $groupConfig;
            }
        }
        if ($normalGroups) {
            _Launcher::checkGroupConfigs($normalGroups);
        }
        if ($queueGroups) {
            LauncherQueue::checkGroupConfigs($queueGroups);
        }
    }

    /**
     * Check if the group is for a feeder/consumers.
     *
     * @param GroupConfigInterface $groupConfig the group to check
     *
     * @return bool true when it's a feeder/consumers, else false
     */
    protected function isQueueGroup(GroupConfigInterface $groupConfig): bool
    {
        foreach ($groupConfig->getProcessConfigs() as $processConfig) {
            if ($processConfig instanceof FeederConfigInterface
                || $processConfig instanceof ConsumerConfigInterface) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProcessHandlersList(): array
    {
        return [
            LocalProcess::class,
            RemoteProcess::class,
            RemoteConsumerProcess::class,
            LocalConsumerProcess::class,
            RemoteFeederProcess::class,
            LocalFeederProcess::class,
        ];
    }
}
