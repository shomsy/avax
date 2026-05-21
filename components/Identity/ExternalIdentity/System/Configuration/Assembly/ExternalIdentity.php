<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Configuration\Assembly;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\ExternalIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityRuntime\ExternalIdentityRuntime;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity as ExternalIdentitySurface;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\Federation;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\OAuth;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\Oidc;

/**
 * Assembles the ExternalIdentity public surface from explicit runtime dependencies.
 */
final class ExternalIdentity
{
    public static function fromStore(ExternalIdentityLinkStoreInterface $store) : ExternalIdentitySurface
    {
        return new ExternalIdentitySurface(
            runtime: new ExternalIdentityRuntime(
                linkStore  : $store,
                oauth      : new OAuth(),
                oidc       : new Oidc(),
                federation : new Federation(),
            ),
        );
    }
}
