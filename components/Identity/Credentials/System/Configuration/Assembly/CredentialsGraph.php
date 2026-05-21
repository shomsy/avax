<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration\Assembly;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\CredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;

/**
 * Configures the Credentials static facade with a backing store.
 *
 * @deprecated Inject CredentialStoreInterface directly instead.
 *             This class remains for backward-compatible assembly.
 */
final class CredentialsGraph
{
    public static function fromStore(CredentialStoreInterface $store) : void
    {
        Credentials::setStore($store);
    }
}
