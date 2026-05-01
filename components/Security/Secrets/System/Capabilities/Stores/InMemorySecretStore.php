<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Capabilities\Stores;

use RuntimeException;

final class InMemorySecretStore implements SecretStore
{
    /** @var array<string, string> */
    private array $secrets = [];

    public function get(string $key): string
    {
        return $this->secrets[$key] ?? throw new RuntimeException("Secret '{$key}' not found");
    }

    public function set(string $key, string $value): void
    {
        $this->secrets[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->secrets[$key]);
    }

    public function forget(string $key): void
    {
        unset($this->secrets[$key]);
    }
}
