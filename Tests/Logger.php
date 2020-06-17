<?php

namespace giudicelli\DistributedArchitectureBundle\Tests;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    private $output = [];

    public function reset()
    {
        $this->output = [];
    }

    public function log($level, $message, array $context = [])
    {
        foreach ($context as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }
        // Remove prefix date
        $message = preg_replace('/^\[[0-9]{4}-[^\]]+\] /', '', $message);
        $this->output[] = "{$level} - {$message}";
    }

    public function getOutput(): array
    {
        return $this->output;
    }
}
