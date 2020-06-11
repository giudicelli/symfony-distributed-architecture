<?php

namespace giudicelli\DistributedArchitectureBundle\Event;

use giudicelli\DistributedArchitecture\Master\EventsInterface;
use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The implementation of EventsInterface, it will dispach events.
 *
 * @author Frédéric Giudicelli
 */
class EventsHandler implements EventsInterface
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function starting(LauncherInterface $launcher, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new MasterStartingEvent($launcher, $logger), MasterStartingEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function started(LauncherInterface $launcher, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new MasterStartedEvent($launcher, $logger), MasterStartedEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function check(LauncherInterface $launcher, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new MasterRunningEvent($launcher, $logger), MasterRunningEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopped(LauncherInterface $launcher, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new MasterStoppedEvent($launcher, $logger), MasterStoppedEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processStarted(ProcessInterface $process, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessStartedEvent($process, $logger), ProcessStartedEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processTimedout(ProcessInterface $process, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessTimedOutEvent($process, $logger), ProcessTimedOutEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processStopped(ProcessInterface $process, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessStoppedEvent($process, $logger), ProcessStoppedEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processWasSeen(ProcessInterface $process, string $line, LoggerInterface $logger): void
    {
        try {
            $this->eventDispatcher->dispatch(new ProcessRunningEvent($process, $line, $logger), ProcessRunningEvent::NAME);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
