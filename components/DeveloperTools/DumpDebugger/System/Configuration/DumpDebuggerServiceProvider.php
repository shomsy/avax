<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\DumpDebugger\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * DumpDebuggerServiceProvider — DeveloperTools/DumpDebugger component.
 *
 * Component is not yet implemented; no DI dependencies to register.
 */
final class DumpDebuggerServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Component not yet implemented — no DI dependencies to register.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
