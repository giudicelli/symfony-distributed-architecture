<?php

namespace giudicelli\DistributedArchitectureBundle\Handler;

trait ProcessTrait
{
    /**
     * Return the basic shell command to execute this process.
     */
    protected function getShellCommand(array $params): string
    {
        if ($this->config->getBinPath()) {
            $bin = $this->config->getBinPath();
        } elseif ($this->groupConfig->getBinPath()) {
            $bin = $this->groupConfig->getBinPath();
        } else {
            $bin = PHP_BINARY;
        }

        $params = escapeshellarg(json_encode($params));

        return $bin.' bin/console '.$this->groupConfig->getCommand().' --gda-params '.$params;
    }
}
