<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * UriServiceProvider — registers HTTP/URI component dependencies.
 *
 * The URI component uses static methods with no external dependencies,
 * so this provider is a no-op placeholder for future extensibility.
 */
final class UriServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // URI component uses static methods with no external dependencies
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
