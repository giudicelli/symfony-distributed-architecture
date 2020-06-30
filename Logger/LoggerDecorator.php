<?php

namespace giudicelli\DistributedArchitectureBundle\Logger;

use giudicelli\DistributedArchitecture\Helper\InterProcessLogger;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * This decorator allows to actually send logs to the master process and to handle slave logs on the master.
 *
 * @author Frédéric Giudicelli
 */
class LoggerDecorator extends AbstractLogger
{
    protected static $isInterprocess = false;

    protected static $isSlave = false;

    /** @var InterProcessLogger */
    protected $interprocessLogger;

    protected $decorated;

    public function __construct(LoggerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public static function configure(bool $isInterprocess, bool $isSlave): void
    {
        self::$isInterprocess = $isInterprocess;
        self::$isSlave = $isSlave;
    }

    public function log($level, $message, array $context = [])
    {
        if (!self::$isInterprocess) {
            $this->decorated->log($level, $message, $context);

            return;
        }

        if (!$this->interprocessLogger) {
            $this->interprocessLogger = new InterProcessLogger(!self::$isSlave, $this->decorated);
        }
        $this->interprocessLogger->log($level, $message, $context);
    }
}
