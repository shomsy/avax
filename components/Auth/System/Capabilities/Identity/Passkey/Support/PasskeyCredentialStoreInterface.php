<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Passkey\Support;

use DateTimeImmutable;

interface PasskeyCredentialStoreInterface
{
    public function save(PasskeyCredential $credential) : void;

    public function find(string $credentialId) : PasskeyCredential|null;

    /**
     * @return list<PasskeyCredential>
     */
    public function forUser(int $userId) : array;

    public function touch(string $credentialId, DateTimeImmutable $usedAt) : void;

    public function rename(string $credentialId, string $label) : void;

    public function revoke(string $credentialId, DateTimeImmutable $revokedAt) : void;

    public function hasActiveCredential(int $userId) : bool;
}
