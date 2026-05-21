<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;
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

        // Admin elevation state is request/runtime scoped. It must not survive worker requests.
        $container->scoped(AdminElevationStore::class, static fn () : AdminElevationStore => new AdminElevationStore());

        // Admin elevation flows
        $container->scoped(BeginAdminElevation::class, static fn (ContainerInterface $c) : BeginAdminElevation => new BeginAdminElevation(
            store: $c->get(AdminElevationStore::class),
        ));
        $container->scoped(EndAdminElevation::class, static fn (ContainerInterface $c) : EndAdminElevation => new EndAdminElevation(
            beginAdminElevation: $c->get(BeginAdminElevation::class),
        ));

        // Access public surface — combines authorization engine + admin elevation
        $container->scoped(Access::class, static function (ContainerInterface $c) : Access {
            $beginAdminElevation = new BeginAdminElevation(
                store: $c->get(AdminElevationStore::class),
            );

            return new Access(
                authorizationEngine: $c->get(AuthorizationEngine::class),
                beginAdminElevation: $beginAdminElevation,
                endAdminElevation  : new EndAdminElevation(
                    beginAdminElevation: $beginAdminElevation,
                ),
            );
        });
    }

    public function boot(ContainerInterface $container) : void
    {
        // Admin elevation state is scoped during registration; no boot-time reset is required.
    }
}
