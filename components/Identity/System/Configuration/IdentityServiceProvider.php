<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\System\Capabilities\IdentityRuntime\IdentityRuntime;
use Avax\Components\Identity\System\Configuration\Builders\IdentityRuntime as BuildIdentityRuntime;
use Avax\Components\Identity\System\Configuration\IdentityConfiguration;

/**
 * IdentityServiceProvider — registers identity aggregate component dependencies.
 */
final class IdentityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        $container->singleton(IdentityConfiguration::class, static fn () : IdentityConfiguration => new IdentityConfiguration());
        $container->singleton(BuildIdentityRuntime::class, static fn () : BuildIdentityRuntime => BuildIdentityRuntime::defaults());
        $container->singleton(
            IdentityRuntime::class,
            static fn () : IdentityRuntime => BuildIdentityRuntime::defaults()->runtime(),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
