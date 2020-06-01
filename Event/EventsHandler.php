<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use giudicelli\DistributedArchitectureBundle\Entity\ProcessStatus;
use giudicelli\DistributedArchitectureBundle\Repository\ProcessStatusRepository;

/**
 * The implementation of EventsInterface stores each process' status into a ProcessStatus entity.
 *
 * @author Frédéric Giudicelli
 */
class EventsHandler implements EventsInterface
{
    protected $processStatusRepository;

    public function __construct(ProcessStatusRepository $processStatusRepository)
    {
        $this->processStatusRepository = $processStatusRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function started(LauncherInterface $launcher): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function check(LauncherInterface $launcher): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function stopped(LauncherInterface $launcher): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function processStarted(ProcessInterface $process): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setStartedAt(new \DateTime());
        $processStatus->setStatus('started');

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function processTimedout(ProcessInterface $process): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setStatus('timedout');

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function processStopped(ProcessInterface $process): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setStoppedAt(new \DateTime());
        $processStatus->setStatus('stopped');

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function processWasSeen(ProcessInterface $process, string $line): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setLastSeenAt(new \DateTime());
        $processStatus->setOutput($line);

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * Return the ProcessStatus entity corresponding to a ProcessInterface process. If it doesn't exist, it is created.
     *
     * @param ProcessInterface $process The process
     *
     * @return ProcessStatus The ProcessStatus entity
     */
    protected function getProcessStatus(ProcessInterface $process): ProcessStatus
    {
        $processStatus = $this->processStatusRepository->find($process->getId());
        if (!$processStatus) {
            $processStatus = new ProcessStatus();
            $processStatus
                ->setId($process->getId())
                ->setGroupId($process->getGroupId())
                ->setGroupName($process->getGroupConfig()->getName())
                ->setHost($process->getHost())
                ->setCommand($process->getGroupConfig()->getCommand())
            ;
        }

        return $processStatus;
    }
}
