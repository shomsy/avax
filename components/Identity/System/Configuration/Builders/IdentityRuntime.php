<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration\Builders;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;
use Avax\Components\Identity\Access\System\Capabilities\AccessRuntime\AccessRuntime;
use Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\Capabilities\AuthenticationRuntime\AuthenticationRuntime;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity as AuthIdentity;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\InMemoryCredentialStore;
use Avax\Components\Identity\Credentials\System\Configuration\Assembly\Credentials as CredentialsAssembly;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\InMemoryExternalIdentityLinkStore;
use Avax\Components\Identity\ExternalIdentity\System\Configuration\Assembly\ExternalIdentity as ExternalIdentityAssembly;
use Avax\Components\Identity\Risk\System\PublicSurface\Risk;
use Avax\Components\Identity\System\Capabilities\GuestSession\GuestSessionIdentity;
use Avax\Components\Identity\System\Capabilities\IdentityRuntime\IdentityRuntime as RootIdentityRuntime;
use Avax\Components\Identity\System\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\DefaultTenantContext;
use Avax\Components\Identity\Tenancy\System\Configuration\Assembly\TenancyGraph;
use Avax\Components\Identity\Tokens\System\Configuration\Assembly\TokensGraph;

/**
 * Assembles the default root Identity runtime used by the fluent public DSL.
 *
 * Note: "Runtime" is defined in .agents/dictionary/framework-terms.md.
 */
final readonly class IdentityRuntime
{
    public function __construct(
        private IdentityConfiguration $configuration,
    ) {}

    public static function defaults(IdentityConfiguration|null $configuration = null) : self
    {
        return new self(
            configuration: $configuration ?? IdentityConfiguration::fromEnvironment(),
        );
    }

    public function runtime() : RootIdentityRuntime
    {
        $adminElevationStore = new AdminElevationStore();
        $beginAdminElevation = new BeginAdminElevation(store: $adminElevationStore);

        $currentAuthentication = new CurrentAuthentication();

        $requireAuthentication = new RequireAuthentication(
            currentAuthentication: $currentAuthentication,
        );
        $requireRole = new RequireRole(
            currentAuthentication: $currentAuthentication,
        );
        $requirePermission = new RequirePermission(
            currentAuthentication: $currentAuthentication,
        );
        $requireResourceOwner = new RequireResourceOwner(
            currentAuthentication: $currentAuthentication,
        );
        $requirePhishingResistant = new RequirePhishingResistantAuthentication(
            currentAuthentication: $currentAuthentication,
        );
        $clock = new Clock();

        $requireFreshMfa = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : $clock,
        );
        $requireAdminElevation = new RequireAdminElevation(
            currentAuthentication: $currentAuthentication,
            adminElevationStore  : new InMemoryAdminElevationStore(),
            clock                : $clock,
        );

        $requireAccessPolicy = new RequireAccessPolicy(
            requireAuthentication          : $requireAuthentication,
            requireRole                    : $requireRole,
            requirePermission              : $requirePermission,
            requireResourceOwner           : $requireResourceOwner,
            requirePhishingResistantAuthentication: $requirePhishingResistant,
            requireFreshMfa                : $requireFreshMfa,
            requireAdminElevation          : $requireAdminElevation,
        );

        return new RootIdentityRuntime(
            auth            : new Auth(
                                  runtime: new AuthenticationRuntime(
                                      identity: AuthIdentity::fromBackends(
                                          sessionIdentity: new GuestSessionIdentity(),
                                      ),
                                  ),
                              ),
            access          : new Access(
                                  runtime: new AccessRuntime(
                                      authentication: $requireAuthentication,
                                      roles           : $requireRole,
                                      permissions     : $requirePermission,
                                      authorization   : new AuthorizationEngine(),
                                      ownership       : $requireResourceOwner,
                                      policies        : $requireAccessPolicy,
                                      elevation       : $beginAdminElevation,
                                      endElevation    : new EndAdminElevation(
                                          beginAdminElevation: $beginAdminElevation,
                                      ),
                                  ),
                              ),
            credentials     : CredentialsAssembly::fromStore(
                store: new InMemoryCredentialStore(),
            ),
            tokens          : TokensGraph::hmac(secret: $this->configuration->requireTokenSecret()),
            tenancy         : TenancyGraph::fromContext(
                context: new DefaultTenantContext(),
            ),
            risk            : new Risk(),
            externalIdentity: ExternalIdentityAssembly::fromStore(
                store: new InMemoryExternalIdentityLinkStore(),
            ),
        );
    }
}
