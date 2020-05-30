<?php

namespace giudicelli\DistributedArchitectureBundle\DependencyInjection;

use giudicelli\DistributedArchitecture\Master\GroupConfigInterface;
use giudicelli\DistributedArchitecture\Master\Handlers\GroupConfig;
use giudicelli\DistributedArchitecture\Master\ProcessConfigInterface;
use giudicelli\DistributedArchitectureBundle\Handler\Local\Config as ConfigLocal;
use giudicelli\DistributedArchitectureBundle\Handler\Remote\Config as ConfigRemote;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DistributedArchitectureExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (empty($config['groups'])) {
            $container->setParameter('distributed_architecture.groups', null);
        } else {
            $container->setParameter('distributed_architecture.groups', $this->parseConfig($config['groups']));
        }
    }

    /** @return GroupConfigInterface[] */
    protected function parseConfig(array $groups): array
    {
        $groupConfigs = [];
        foreach ($groups as $name => $group) {
            $groupConfig = ['name' => $name];
            $processes = [];
            foreach ($group as $key => $value) {
                $key = $this->fixSnakeCase($key);
                switch ($key) {
                    case 'local':
                        $processes[] = $this->parseProcessConfig($value, ConfigLocal::class);

                    break;
                    case 'remote':
                        foreach ($value as $remote) {
                            $processes[] = $this->parseProcessConfig($remote, ConfigRemote::class);
                        }

                    break;
                    default:
                        $groupConfig[$key] = $value;

                    break;
                }
            }
            $groupConfigObject = new GroupConfig();
            $groupConfigObject->fromArray($groupConfig);
            $groupConfigObject->setProcessConfigs($processes);
            $groupConfigs[] = $groupConfigObject;
        }

        return $groupConfigs;
    }

    protected function parseProcessConfig(array $config, string $class): ProcessConfigInterface
    {
        $processConfig = [];
        foreach ($config as $key => $value) {
            $processConfig[$this->fixSnakeCase($key)] = $value;
        }
        $processConfigObject = new $class();
        $processConfigObject->fromArray($processConfig);

        return $processConfigObject;
    }

    protected function fixSnakeCase(string $value): string
    {
        $parts = explode('_', $value);
        if (1 === count($parts)) {
            return $value;
        }
        for ($i = 1; $i < count($parts); ++$i) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return join('', $parts);
    }
}
