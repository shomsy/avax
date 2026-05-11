<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\Keys;

final class InMemoryIdempotencyStore implements IdempotencyStore
{
    /** @var array<string, array{value: array, expires_at: int}> */
    private array $store = [];

    public function set(string $key, array $value, int $ttl): void
    {
        $this->store[$key] = [
            'value' => $value,
            'expires_at' => time() + $ttl,
        ];
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function get(string $key) : array|null
    {
        if (! isset($this->store[$key])) {
            return null;
        }

        $entry = $this->store[$key];

        if ($entry['expires_at'] < time()) {
            unset($this->store[$key]);

            return null;
        }

        return $entry['value'];
    }
}
