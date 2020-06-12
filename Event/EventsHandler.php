<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The implementation of EventsInterface, it will dispach events.
 *
 * @author Frédéric Giudicelli
 */
class EventsHandler implements EventsInterface
{
    protected $eventDispatcher;

    private $lastMasterRunning = 0;

    private $lastProcessesRunning = [];

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function starting(LauncherInterface $launcher): void
    {
        $this->lastProcessesRunning = [];

        try {
            $this->eventDispatcher->dispatch(new MasterStartingEvent($launcher), MasterStartingEvent::NAME);
        } catch (\Exception $e) {
            $launcher->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function started(LauncherInterface $launcher): void
    {
        try {
            $this->eventDispatcher->dispatch(new MasterStartedEvent($launcher), MasterStartedEvent::NAME);
        } catch (\Exception $e) {
            $launcher->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function check(LauncherInterface $launcher): void
    {
        // Avoid flooding
        if ($this->lastMasterRunning
           && (time() - $this->lastMasterRunning) < 1) {
            return;
        }
        $this->lastMasterRunning = time();

        try {
            $this->eventDispatcher->dispatch(new MasterRunningEvent($launcher), MasterRunningEvent::NAME);
        } catch (\Exception $e) {
            $launcher->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopped(LauncherInterface $launcher): void
    {
        $this->lastProcessesRunning = [];

        try {
            $this->eventDispatcher->dispatch(new MasterStoppedEvent($launcher), MasterStoppedEvent::NAME);
        } catch (\Exception $e) {
            $launcher->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processStarted(ProcessInterface $process): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessStartedEvent($process), ProcessStartedEvent::NAME);
        } catch (\Exception $e) {
            $process->getParent()->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processTimedout(ProcessInterface $process): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessTimedOutEvent($process), ProcessTimedOutEvent::NAME);
        } catch (\Exception $e) {
            $process->getParent()->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processStopped(ProcessInterface $process): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessStoppedEvent($process), ProcessStoppedEvent::NAME);
        } catch (\Exception $e) {
            $process->getParent()->getLogger()->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processWasSeen(ProcessInterface $process, string $line): void
    {
        // Avoid flooding
        if (!empty($this->lastProcessesRunning[$process->getId()])
        && (time() - $this->lastProcessesRunning[$process->getId()]['t']) < 1
        && $this->lastProcessesRunning[$process->getId()]['l'] === $line) {
            return;
        }
        $this->lastProcessesRunning[$process->getId()] = ['t' => time(), 'l' => $line];

        try {
            $this->eventDispatcher->dispatch(new ProcessRunningEvent($process, $line), ProcessRunningEvent::NAME);
        } catch (\Exception $e) {
            $process->getParent()->getLogger()->error($e->getMessage());
        }
    }
}
