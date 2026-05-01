<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support;

use DateTimeImmutable;
use SensitiveParameter;

final class InMemoryPasskeyCredentialStore implements PasskeyCredentialStoreInterface
{
    /** @var array<string, PasskeyCredential> */
    private array $credentials = [];

    public function save(#[SensitiveParameter] PasskeyCredential $passkeyCredential) : void
    {
        $this->credentials[$passkeyCredential->credentialId] = $passkeyCredential;
    }

    public function find(#[SensitiveParameter] string $credentialId): ?PasskeyCredential
    {
        return $this->credentials[$credentialId] ?? null;
    }

    public function touch(#[SensitiveParameter] string $credentialId, DateTimeImmutable $usedAt): void
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
            revokedAt   : $credential->revokedAt,
        );
    }

    public function rename(#[SensitiveParameter] string $credentialId, string $label): void
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
            revokedAt   : $credential->revokedAt,
        );
    }

    public function revoke(#[SensitiveParameter] string $credentialId, DateTimeImmutable $revokedAt): void
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
            revokedAt   : $revokedAt,
        );
    }

    public function hasActiveCredential(int $userId): bool
    {
        return array_any(
            array   : $this->forUser(userId: $userId),
            callback: static fn (#[SensitiveParameter] PasskeyCredential $passkeyCredential) : bool => ! $passkeyCredential->isRevoked(),
        );
    }

    public function forUser(int $userId): array
    {
        return array_values(array: array_filter(
            array   : $this->credentials,
            callback: static fn (#[SensitiveParameter] PasskeyCredential $passkeyCredential) : bool => $passkeyCredential->userId === $userId,
        ));
    }
}
