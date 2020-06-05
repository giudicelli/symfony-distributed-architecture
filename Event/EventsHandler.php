<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use giudicelli\DistributedArchitectureBundle\Entity\MasterCommand;
use giudicelli\DistributedArchitectureBundle\Entity\ProcessStatus;
use giudicelli\DistributedArchitectureBundle\Repository\MasterCommandRepository;
use giudicelli\DistributedArchitectureBundle\Repository\ProcessStatusRepository;
use Psr\Log\LoggerInterface;

/**
 * The implementation of EventsInterface stores each process' status into a ProcessStatus entity.
 *
 * @author Frédéric Giudicelli
 */
class EventsHandler implements EventsInterface
{
    protected $processStatusRepository;
    protected $masterCommandRepository;

    public function __construct(ProcessStatusRepository $processStatusRepository, MasterCommandRepository $masterCommandRepository)
    {
        $this->processStatusRepository = $processStatusRepository;
        $this->processStatusRepository = $masterCommandRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function started(LauncherInterface $launcher, LoggerInterface $logger): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function check(LauncherInterface $launcher, LoggerInterface $logger): void
    {
        // Only the master launcher takes commands
        if (!$launcher->isMaster()) {
            return;
        }

        $masterCommand = $this->processStatusRepository->findOnePending();
        if (!$masterCommand) {
            return;
        }
        $masterCommand->setStatus(MasterCommand::STATUS_INPROGRESS);
        $this->processStatusRepository->update($masterCommand);

        switch ($masterCommand->getCommand()) {
            case MasterCommand::COMMAND_START_ALL:
                $launcher->runAll();

            break;
            case MasterCommand::COMMAND_STOP_ALL:
                $params = $masterCommand->getParams();
                $launcher->stopAll(!empty($params['force']));

            break;
            case MasterCommand::COMMAND_START_GROUP:
                $launcher->runGroup($masterCommand->getGroupName());

            break;
            case MasterCommand::COMMAND_STOP_GROUP:
                $params = $masterCommand->getParams();
                $launcher->stopGroup($masterCommand->getGroupName(), !empty($params['force']));

            break;
            case MasterCommand::COMMAND_STOP:
                $launcher->stop();

            break;
        }

        $masterCommand
            ->setStatus(MasterCommand::STATUS_DONE)
            ->setHandledAt(new \DateTime())
        ;
        $this->processStatusRepository->update($masterCommand);
    }

    /**
     * {@inheritdoc}
     */
    public function stopped(LauncherInterface $launcher, LoggerInterface $logger): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function processStarted(ProcessInterface $process, LoggerInterface $logger): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setStartedAt(new \DateTime());
        $processStatus->setStatus('started');

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function processTimedout(ProcessInterface $process, LoggerInterface $logger): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setStatus('timedout');

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function processStopped(ProcessInterface $process, LoggerInterface $logger): void
    {
        $processStatus = $this->getProcessStatus($process);
        $processStatus->setStoppedAt(new \DateTime());
        $processStatus->setStatus('stopped');

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function processWasSeen(ProcessInterface $process, string $line, LoggerInterface $logger): void
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
                ->setHost(gethostname())
                ->setCommand($process->getGroupConfig()->getCommand())
            ;
        }

        return $processStatus;
    }
}
