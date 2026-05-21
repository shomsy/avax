<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore;

/**
 * In-memory credential store — suitable for testing and simple setups.
 * NOT suitable for long-lived workers without per-request reset.
 */
final class InMemoryCredentialStore implements CredentialStoreInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $store = [];

    public function store(string $userId, array $credentials) : void
    {
        $this->store[$userId] = $credentials;
    }

    public function read(string $userId) : array|null
    {
        return $this->store[$userId] ?? null;
    }

    public function forget(string $userId) : void
    {
        unset($this->store[$userId]);
    }
}
