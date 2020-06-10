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
        $this->output[] = "{$level} - {$message}";
    }

    public function getOutput(): array
    {
        return $this->output;
    }
}
