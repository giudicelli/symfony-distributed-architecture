<?php

namespace giudicelli\DistributedArchitectureBundle\Handler;

/**
 * This Trait is used by both remote and local process to generate the proper shell command to start a Symfony process.
 *
 * @author Frédéric Giudicelli
 *
 * @internal
 */
trait ProcessTrait
{
    /**
     * {@inheritdoc}
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
