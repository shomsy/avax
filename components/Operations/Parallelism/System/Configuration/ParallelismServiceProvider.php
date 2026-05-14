<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelismConfig;

/**
 * ParallelismServiceProvider — registers parallelism component dependencies.
 */
final class ParallelismServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Parallelism configuration — worker count and runtime settings
        $container->singleton(ParallelismConfig::class, static fn () : ParallelismConfig => new ParallelismConfig());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
