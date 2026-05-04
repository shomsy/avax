<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\Drivers;

use Avax\Framework\System\Capabilities\ExternalState\System\PublicSurface\State;

/**
 * In-memory implementation of external state (for testing or local dev).
 */
final class Memory implements State
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $this->data[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function increment(string $key, int $value = 1): int
    {
        $this->data[$key] = ($this->data[$key] ?? 0) + $value;
        return (int) $this->data[$key];
    }

    public function expire(string $key, int $ttl): void
    {
        // TTL not implemented in memory driver for now
    }
}
