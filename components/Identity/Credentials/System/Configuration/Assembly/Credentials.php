<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration\Assembly;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\CredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\CredentialsRuntime\CredentialsRuntime;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials as CredentialsSurface;
use Avax\Components\Identity\Credentials\System\PublicSurface\Mfa;
use Avax\Components\Identity\Credentials\System\PublicSurface\Passkeys;
use Avax\Components\Identity\Credentials\System\PublicSurface\Passwords;

/**
 * Assembles the Credentials public surface from explicit runtime dependencies.
 */
final class Credentials
{
    public static function fromStore(CredentialStoreInterface $store) : CredentialsSurface
    {
        return new CredentialsSurface(
            runtime: new CredentialsRuntime(
                credentialStore: $store,
                mfa            : new Mfa(),
                passkeys       : new Passkeys(),
                passwords      : new Passwords(),
            ),
        );
    }
}
