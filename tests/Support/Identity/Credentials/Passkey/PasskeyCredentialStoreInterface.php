<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

use DateTimeImmutable;

interface PasskeyCredentialStoreInterface
{
    public function save(PasskeyCredential $passkeyCredential): void;

    public function find(string $credentialId): ?PasskeyCredential;

    /**
     * @return list<PasskeyCredential>
     */
    public function forUser(int $userId): array;

    public function touch(string $credentialId, DateTimeImmutable $usedAt): void;

    public function rename(string $credentialId, string $label): void;

    public function revoke(string $credentialId, DateTimeImmutable $revokedAt): void;

    public function hasActiveCredential(int $userId): bool;
}
