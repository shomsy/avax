<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\PublicSurface\Access;

/**
 * AccessServiceProvider — registers access component dependencies.
 */
final class AccessServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Authorization engine — permission checking engine
        $container->singleton(AuthorizationEngine::class, static fn () : AuthorizationEngine => new AuthorizationEngine());

        // Admin elevation flows
        $container->singleton(BeginAdminElevation::class, static fn () : BeginAdminElevation => new BeginAdminElevation());
        $container->singleton(EndAdminElevation::class, static fn () : EndAdminElevation => new EndAdminElevation());

        // Access public surface — combines authorization engine + admin elevation
        $container->singleton(Access::class, static fn (ContainerInterface $c) : Access => new Access(
            authorizationEngine: $c->get(AuthorizationEngine::class),
            beginAdminElevation: $c->get(BeginAdminElevation::class),
            endAdminElevation  : $c->get(EndAdminElevation::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // Reset static elevation state for worker safety
        BeginAdminElevation::reset();
    }
}
