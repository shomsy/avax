<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration\Assembly;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\CredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\CredentialsRuntime\CredentialsRuntime;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\Credentials\System\PublicSurface\Mfa;
use Avax\Components\Identity\Credentials\System\PublicSurface\Passkey;

/**
 * Assembles the Credentials public surface from explicit runtime dependencies.
 */
final class CredentialsGraph
{
    public static function fromStore(CredentialStoreInterface $store) : Credentials
    {
        return new Credentials(
            runtime: new CredentialsRuntime(
                credentialStore: $store,
                mfa            : new Mfa(),
                passkey        : new Passkey(),
            ),
        );
    }
}
