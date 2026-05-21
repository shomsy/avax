<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;
use Avax\Components\Identity\Access\System\Capabilities\AccessRuntime\AccessRuntime;
use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Engine\PolicyEvaluator;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Policy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;

/**
 * AccessServiceProvider — registers access component dependencies.
 */
final class AccessServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Authorization engine — permission checking engine
        $container->singleton(AuthorizationEngine::class, static fn () : AuthorizationEngine => new AuthorizationEngine());

        // Policy rules and named policy definitions are runtime scoped.
        $container->scoped(PolicyEvaluator::class, static fn () : PolicyEvaluator => new PolicyEvaluator());
        $container->scoped(Policy::class, static fn (ContainerInterface $c) : Policy => new Policy(
            policyEvaluator: $c->get(PolicyEvaluator::class),
        ));

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
            $currentAuth = new CurrentAuthentication();
            $clock = new Clock();
            $requireResourceOwner = new RequireResourceOwner(currentAuthentication: $currentAuth);
            $requireAccessPolicy = new RequireAccessPolicy(
                requireAuthentication: new RequireAuthentication(currentAuthentication: $currentAuth),
                requireRole: new RequireRole(currentAuthentication: $currentAuth),
                requirePermission: new RequirePermission(currentAuthentication: $currentAuth),
                requireResourceOwner: $requireResourceOwner,
                requirePhishingResistantAuthentication: new RequirePhishingResistantAuthentication(currentAuthentication: $currentAuth),
                requireFreshMfa: new RequireFreshMfa(currentAuthentication: $currentAuth, clock: $clock),
                requireAdminElevation: new RequireAdminElevation(currentAuthentication: $currentAuth, adminElevationStore: new InMemoryAdminElevationStore(), clock: $clock),
            );

            return new Access(
                runtime: new AccessRuntime(
                    authentication: new RequireAuthentication(currentAuthentication: $currentAuth),
                    roles           : new RequireRole(currentAuthentication: $currentAuth),
                    permissions     : new RequirePermission(currentAuthentication: $currentAuth),
                    authorization   : $c->get(AuthorizationEngine::class),
                    ownership       : $requireResourceOwner,
                    policies        : $requireAccessPolicy,
                    elevation       : $beginAdminElevation,
                    endElevation    : new EndAdminElevation(
                        beginAdminElevation: $beginAdminElevation,
                    ),
                ),
            );
        });
    }

    public function boot(ContainerInterface $container) : void
    {
        // Admin elevation state is scoped during registration; no boot-time reset is required.
    }
}
