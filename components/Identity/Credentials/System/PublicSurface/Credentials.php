<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\PublicSurface;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialsRuntime\CredentialsRuntime;

/**
 * Credentials — manages user credential data.
 */
final readonly class Credentials
{
    public function __construct(
        private CredentialsRuntime $runtime,
    ) {}

    /**
     * @param array<string, mixed> $credentials
     */
    public function store(string $userId, array $credentials) : void
    {
        $this->runtime->store(
            userId     : $userId,
            credentials: $credentials,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $userId) : array|null
    {
        return $this->runtime->read(userId: $userId);
    }

    public function forget(string $userId) : void
    {
        $this->runtime->forget(userId: $userId);
    }

    public function mfa() : Mfa
    {
        return $this->runtime->mfa();
    }

    public function passkeys() : Passkey
    {
        return $this->runtime->passkeys();
    }
}
