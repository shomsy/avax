<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;

/**
 * AuthServiceProvider — registers Identity/Auth component dependencies.
 */
final class AuthServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Identity capability coordinator — all dependencies are optional
        $container->singleton(Identity::class, static fn () : Identity => new Identity());

        // Auth facade — requires Identity
        $container->singleton(AuthInterface::class, static fn (ContainerInterface $c) : Auth => new Auth(
            identity: $c->get(Identity::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
