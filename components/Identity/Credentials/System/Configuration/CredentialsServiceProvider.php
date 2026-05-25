<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Credentials\System\Configuration\Graphs\CredentialsGraph;

/**
 * CredentialsServiceProvider — delegates to CredentialsGraph for all registrations.
 */
final class CredentialsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        CredentialsGraph::register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        CredentialsGraph::boot();
    }
}
