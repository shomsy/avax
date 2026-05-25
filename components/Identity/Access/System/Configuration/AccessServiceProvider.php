<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Configuration\Graphs\AccessGraph;

/**
 * AccessServiceProvider — service provider for the Access component.
 *
 * Delegates registration and boot to AccessGraph for complete DI assembly
 * of all Access/Authorization capabilities, boundaries, and the public surface.
 */
final class AccessServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        AccessGraph::register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        AccessGraph::boot($container);
    }
}
