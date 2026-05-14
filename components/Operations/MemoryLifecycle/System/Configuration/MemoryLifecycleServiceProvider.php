<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\MemoryLifecycle\System\Configuration\MemoryLifecycleConfiguration;

/**
 * MemoryLifecycleServiceProvider — registers memory lifecycle component dependencies.
 */
final class MemoryLifecycleServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Memory lifecycle configuration
        $container->singleton(MemoryLifecycleConfiguration::class, static fn () : MemoryLifecycleConfiguration => new MemoryLifecycleConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
