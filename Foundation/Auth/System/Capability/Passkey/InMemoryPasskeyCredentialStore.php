<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

use DateTimeImmutable;

final class InMemoryPasskeyCredentialStore implements PasskeyCredentialStoreInterface
{
    /** @var array<string, PasskeyCredential> */
    private array $credentials = [];

    public function save(PasskeyCredential $credential) : void
    {
        $this->credentials[$credential->credentialId] = $credential;
    }

    public function find(string $credentialId) : PasskeyCredential|null
    {
        return $this->credentials[$credentialId] ?? null;
    }

    public function forUser(int $userId) : array
    {
        return array_values(array_filter(
            $this->credentials,
            static fn (PasskeyCredential $credential) : bool => $credential->userId === $userId
        ));
    }

    public function touch(string $credentialId, DateTimeImmutable $usedAt) : void
    {
        $credential = $this->credentials[$credentialId] ?? null;

        if ($credential === null) {
            return;
        }

        $this->credentials[$credentialId] = new PasskeyCredential(
            userId      : $credential->userId,
            credentialId: $credential->credentialId,
            label       : $credential->label,
            registeredAt: $credential->registeredAt,
            lastUsedAt  : $usedAt,
            revokedAt   : $credential->revokedAt
        );
    }

    public function rename(string $credentialId, string $label) : void
    {
        $credential = $this->credentials[$credentialId] ?? null;

        if ($credential === null) {
            return;
        }

        $this->credentials[$credentialId] = new PasskeyCredential(
            userId      : $credential->userId,
            credentialId: $credential->credentialId,
            label       : $label,
            registeredAt: $credential->registeredAt,
            lastUsedAt  : $credential->lastUsedAt,
            revokedAt   : $credential->revokedAt
        );
    }

    public function revoke(string $credentialId, DateTimeImmutable $revokedAt) : void
    {
        $credential = $this->credentials[$credentialId] ?? null;

        if ($credential === null) {
            return;
        }

        $this->credentials[$credentialId] = new PasskeyCredential(
            userId      : $credential->userId,
            credentialId: $credential->credentialId,
            label       : $credential->label,
            registeredAt: $credential->registeredAt,
            lastUsedAt  : $credential->lastUsedAt,
            revokedAt   : $revokedAt
        );
    }

    public function hasActiveCredential(int $userId) : bool
    {
        foreach ($this->forUser($userId) as $credential) {
            if (! $credential->isRevoked()) {
                return true;
            }
        }

        return false;
    }
}
