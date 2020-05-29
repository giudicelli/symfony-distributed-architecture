<?php

namespace giudicelli\DistributedArchitectureBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Frédéric Giudicelli
 */
class DistributedArchitectureBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        // noop
    }
}
