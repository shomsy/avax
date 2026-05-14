<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\HealthCheck\CheckContainerHealth;
use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Application\Container\System\PublicSurface\Container;

/**
 * ContainerServiceProvider — registers the container itself as a service.
 *
 * This enables other components to depend on ContainerInterface and
 * resolves self-referential dependencies through the container.
 */
final class ContainerServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Register the container as itself (self-reference)
        $container->instance(ContainerInterface::class, $container);

        // ResolveCallable — central callable/class-string resolver
        $container->singleton(ResolveCallable::class, static fn (ContainerInterface $c) : ResolveCallable => new ResolveCallable(container: $c));

        // Health check
        $container->singleton(CheckContainerHealth::class, static fn () : CheckContainerHealth => new CheckContainerHealth());
    }

    public function boot(ContainerInterface $container) : void
    {
        // Wire static facade for backward compatibility
        Container::setContainer($container);
    }
}
