<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Configuration\Assembly;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\ExternalIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityRuntime\ExternalIdentityRuntime;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;

/**
 * Assembles the ExternalIdentity public surface from explicit runtime dependencies.
 */
final class ExternalIdentityGraph
{
    public static function fromStore(ExternalIdentityLinkStoreInterface $store) : ExternalIdentity
    {
        return new ExternalIdentity(
            runtime: new ExternalIdentityRuntime(linkStore: $store),
        );
    }
}
