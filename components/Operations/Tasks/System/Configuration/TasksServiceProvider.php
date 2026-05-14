<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Tasks\System\Configuration\TasksConfiguration;

/**
 * TasksServiceProvider — registers tasks component dependencies.
 */
final class TasksServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Tasks configuration
        $container->singleton(TasksConfiguration::class, static fn () : TasksConfiguration => new TasksConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
