<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * SecureRequestServiceProvider — registers secure request component dependencies.
 */
final class SecureRequestServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // No DI registrations needed — SecureRequest is an abstract base class
        // that delegates to DataTransfer for hydration and validation.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
