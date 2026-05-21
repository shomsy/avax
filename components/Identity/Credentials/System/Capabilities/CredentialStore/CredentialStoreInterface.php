<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore;

/**
 * Storage interface for credential data per user.
 */
interface CredentialStoreInterface
{
    /**
     * @param array<string, mixed> $credentials
     */
    public function store(string $userId, array $credentials) : void;

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $userId) : array|null;

    public function forget(string $userId) : void;
}
