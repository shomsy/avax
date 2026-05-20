<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * DiagnosticsServiceProvider — DeveloperTools/Diagnostics component.
 *
 * All Diagnostics methods are static utility helpers; no DI dependencies to register.
 */
final class DiagnosticsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // No DI dependencies — all Diagnostics classes use static methods only.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
