<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\System\Configuration\IdentityConfig;

/**
 * IdentityServiceProvider — registers identity aggregate component dependencies.
 */
final class IdentityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Identity configuration
        $container->singleton(IdentityConfig::class, static fn () : IdentityConfig => new IdentityConfig());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
