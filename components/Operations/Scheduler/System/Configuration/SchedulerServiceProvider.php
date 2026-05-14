<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * SchedulerServiceProvider — registers scheduler component dependencies.
 */
final class SchedulerServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // No DI registrations needed — Scheduler uses static state internally
    }

    public function boot(ContainerInterface $container) : void
    {
        // Reset scheduler static state for worker safety
    }
}
