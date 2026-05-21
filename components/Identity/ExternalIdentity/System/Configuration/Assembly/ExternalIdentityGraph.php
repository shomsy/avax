<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Configuration\Assembly;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\ExternalIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;

/**
 * Configures the ExternalIdentity static facade with a backing store.
 *
 * @deprecated Inject ExternalIdentityLinkStoreInterface directly instead.
 *             This class remains for backward-compatible assembly.
 */
final class ExternalIdentityGraph
{
    public static function fromStore(ExternalIdentityLinkStoreInterface $store) : void
    {
        ExternalIdentity::setLinkStore($store);
    }
}
