<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * MiddlewareServiceProvider — HTTP middleware component assembly entrypoint.
 *
 * Status: SCAFFOLD — This component is not yet runtime-critical.
 * The ServiceProvider provides a registration point for future middleware
 * pipeline configuration. No bindings are registered until the component
 * has real behavior to assemble.
 */
final class MiddlewareServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // SCAFFOLD — no bindings until component has real behavior
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
