<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\BackgroundProcesses\System\Configuration\BackgroundProcessesConfiguration;

/**
 * BackgroundProcessesServiceProvider — registers background processes component dependencies.
 */
final class BackgroundProcessesServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Background processes configuration
        $container->singleton(BackgroundProcessesConfiguration::class, static fn () : BackgroundProcessesConfiguration => new BackgroundProcessesConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
