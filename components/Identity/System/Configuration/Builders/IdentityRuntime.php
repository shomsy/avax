<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration\Builders;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;
use Avax\Components\Identity\Access\System\Capabilities\AccessRuntime\AccessRuntime;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\Capabilities\AuthenticationRuntime\AuthenticationRuntime;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity as AuthIdentity;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\InMemoryExternalIdentityLinkStore;
use Avax\Components\Identity\ExternalIdentity\System\Configuration\Assembly\ExternalIdentityGraph;
use Avax\Components\Identity\Risk\System\PublicSurface\Risk;
use Avax\Components\Identity\System\Capabilities\GuestSession\GuestSessionIdentity;
use Avax\Components\Identity\System\Capabilities\IdentityRuntime\IdentityRuntime as RootIdentityRuntime;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use Avax\Components\Identity\Tokens\System\Configuration\Assembly\TokensGraph;

/**
 * Assembles the default root Identity runtime used by the fluent public DSL.
 */
final readonly class IdentityRuntime
{
    public static function defaults() : self
    {
        return new self();
    }

    public function runtime() : RootIdentityRuntime
    {
        $adminElevationStore = new AdminElevationStore();
        $beginAdminElevation = new BeginAdminElevation(store: $adminElevationStore);

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
                                      authorizationEngine: new AuthorizationEngine(),
                                      beginAdminElevation: $beginAdminElevation,
                                      endAdminElevation  : new EndAdminElevation(
                                          beginAdminElevation: $beginAdminElevation,
                                      ),
                                  ),
                              ),
            credentials     : new Credentials(),
            tokens          : TokensGraph::hmac(secret: 'test'),
            tenancy         : new Tenancy(),
            risk            : new Risk(),
            externalIdentity: ExternalIdentityGraph::fromStore(
                store: new InMemoryExternalIdentityLinkStore(),
            ),
        );
    }
}
