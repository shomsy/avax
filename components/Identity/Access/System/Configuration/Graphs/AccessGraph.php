<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Configuration\Graphs;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\Authorization\Authorization;
use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Engine\PolicyEvaluator;
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
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;

/**
 * AccessGraph — assembles the Access/Authorization capability tree from DI.
 *
 * Builds two tiers:
 *   1. PublicSurface (Access) — allows/denies/authorize + admin elevation
 *   2. Capabilities (Authorization) — requireAuthentication/Role/Permission/Policy
 *
 * All Require* boundaries and the RequireAccessPolicy orchestrator are
 * registered as singletons with their full dependency chains resolved.
 */
final class AccessGraph
{
    /**
     * Register all Access/Authorization services in the container.
     */
    public static function register(ContainerInterface $container) : void
    {
        // === Foundation ===

        // AuthorizationEngine — simple permission store
        $container->singleton(
            AuthorizationEngine::class,
            static fn () : AuthorizationEngine => new AuthorizationEngine(),
        );

        // PolicyEvaluator — rule-based policy evaluation engine
        $container->singleton(
            PolicyEvaluator::class,
            static fn () : PolicyEvaluator => new PolicyEvaluator(),
        );

        // Admin elevation flows (static state — reset in boot)
        $container->singleton(
            BeginAdminElevation::class,
            static fn () : BeginAdminElevation => new BeginAdminElevation(),
        );
        $container->singleton(
            EndAdminElevation::class,
            static fn () : EndAdminElevation => new EndAdminElevation(),
        );

        // === Require* Boundaries ===

        // RequireAuthentication — checks auth presence
        $container->singleton(
            RequireAuthentication::class,
            static fn (ContainerInterface $c) : RequireAuthentication => new RequireAuthentication(
                $c->get(CurrentAuthentication::class),
            ),
        );

        // RequireRole — checks user role
        $container->singleton(
            RequireRole::class,
            static fn (ContainerInterface $c) : RequireRole => new RequireRole(
                $c->get(CurrentAuthentication::class),
            ),
        );

        // RequirePermission — checks user permission
        $container->singleton(
            RequirePermission::class,
            static fn (ContainerInterface $c) : RequirePermission => new RequirePermission(
                $c->get(CurrentAuthentication::class),
            ),
        );

        // RequireResourceOwner — checks resource ownership
        $container->singleton(
            RequireResourceOwner::class,
            static fn (ContainerInterface $c) : RequireResourceOwner => new RequireResourceOwner(
                $c->get(CurrentAuthentication::class),
            ),
        );

        // RequirePhishingResistantAuthentication — checks phishing-resistant auth
        $container->singleton(
            RequirePhishingResistantAuthentication::class,
            static fn (ContainerInterface $c) : RequirePhishingResistantAuthentication => new RequirePhishingResistantAuthentication(
                $c->get(CurrentAuthentication::class),
            ),
        );

        // RequireFreshMfa — step-up MFA guard (from Credentials component)
        $container->singleton(
            RequireFreshMfa::class,
            static fn (ContainerInterface $c) : RequireFreshMfa => new RequireFreshMfa(
                $c->get(CurrentAuthentication::class),
                $c->get(\Avax\Components\Identity\Auth\System\Foundation\Clock::class),
            ),
        );

        // RequireAdminElevation — checks admin elevation (from Tenancy component)
        $container->singleton(
            RequireAdminElevation::class,
            static fn (ContainerInterface $c) : RequireAdminElevation => new RequireAdminElevation(
                $c->get(CurrentAuthentication::class),
                $c->get(AdminElevationStoreInterface::class),
                $c->get(\Avax\Components\Identity\Auth\System\Foundation\Clock::class),
            ),
        );

        // RequireAccessPolicy — orchestrates all requirement checks
        $container->singleton(
            RequireAccessPolicy::class,
            static fn (ContainerInterface $c) : RequireAccessPolicy => new RequireAccessPolicy(
                requireAuthentication             : $c->get(RequireAuthentication::class),
                requireRole                       : $c->get(RequireRole::class),
                requirePermission                 : $c->get(RequirePermission::class),
                requireResourceOwner              : $c->get(RequireResourceOwner::class),
                requirePhishingResistantAuthentication: $c->get(RequirePhishingResistantAuthentication::class),
                requireFreshMfa                   : $c->get(RequireFreshMfa::class),
                requireAdminElevation             : $c->get(RequireAdminElevation::class),
            ),
        );

        // === Authorization Capability ===

        // Authorization — root authorization logic with all 4 boundaries
        $container->singleton(
            Authorization::class,
            static fn (ContainerInterface $c) : Authorization => new Authorization(
                requireAuthenticationBoundary: $c->get(RequireAuthentication::class),
                requireRoleBoundary          : $c->get(RequireRole::class),
                requirePermissionBoundary    : $c->get(RequirePermission::class),
                requireAccessPolicyBoundary  : $c->get(RequireAccessPolicy::class),
            ),
        );

        // === PublicSurface ===

        // Access — public entry point combining authorization engine + admin elevation
        $container->singleton(
            Access::class,
            static fn (ContainerInterface $c) : Access => new Access(
                authorizationEngine: $c->get(AuthorizationEngine::class),
                beginAdminElevation: $c->get(BeginAdminElevation::class),
                endAdminElevation  : $c->get(EndAdminElevation::class),
            ),
        );
    }

    /**
     * Boot-time actions: reset static state for worker safety.
     */
    public static function boot(ContainerInterface $container) : void
    {
        BeginAdminElevation::reset();
        \Avax\Components\Identity\Access\System\Capabilities\Policy\Policy::reset();
    }
}
