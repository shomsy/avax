<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\CredentialsRuntime;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\CredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\PublicSurface\Mfa;
use Avax\Components\Identity\Credentials\System\PublicSurface\Passkey;

/**
 * CredentialsRuntime owns credential storage behavior for one assembled runtime.
 */
final readonly class CredentialsRuntime
{
    public function __construct(
        private CredentialStoreInterface $credentialStore,
        private Mfa                      $mfa,
        private Passkey                  $passkey,
    ) {}

    /**
     * @param array<string, mixed> $credentials
     */
    public function store(string $userId, array $credentials) : void
    {
        $this->credentialStore->store(
            userId     : $userId,
            credentials: $credentials,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $userId) : array|null
    {
        return $this->credentialStore->read(userId: $userId);
    }

    public function forget(string $userId) : void
    {
        $this->credentialStore->forget(userId: $userId);
    }

    public function mfa() : Mfa
    {
        return $this->mfa;
    }

    public function passkeys() : Passkey
    {
        return $this->passkey;
    }
}
