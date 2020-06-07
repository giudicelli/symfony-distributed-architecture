<?php

namespace giudicelli\DistributedArchitectureBundle\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Simple logger to add the date.
 *
 * @author Frédéric Giudicelli
 */
class ServiceLogger extends AbstractLogger
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, '['.date('Y-m-d H:i:s').'] '.$message, $context);
    }
}
