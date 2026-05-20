<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * DxServiceProvider — DeveloperTools/Dx component.
 *
 * All Dx classes are pure builders with no DI dependencies.
 */
final class DxServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // No DI dependencies — all Dx classes are pure builders.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
