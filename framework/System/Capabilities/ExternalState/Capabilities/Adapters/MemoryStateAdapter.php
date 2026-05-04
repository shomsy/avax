<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters;

use Avax\Framework\System\Capabilities\ExternalState\PublicSurface\StateAdapter;

final class MemoryStateAdapter implements StateAdapter
{
    /** @var array<string, mixed> */
    private array $store = [];

    /** @var array<string, int> */
    private array $ttls = [];

    public function get(string $key): mixed
    {
        if (isset($this->ttls[$key]) && $this->ttls[$key] < time()) {
            unset($this->store[$key], $this->ttls[$key]);

            return null;
        }

        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $this->store[$key] = $value;

        if ($ttl > 0) {
            $this->ttls[$key] = time() + $ttl;
        } else {
            unset($this->ttls[$key]);
        }
    }

    public function delete(string $key): void
    {
        unset($this->store[$key], $this->ttls[$key]);
    }

    public function exists(string $key): bool
    {
        return isset($this->store[$key]) && (!isset($this->ttls[$key]) || $this->ttls[$key] >= time());
    }

    public function increment(string $key, int $value = 1): int
    {
        $current = (int)($this->store[$key] ?? 0);
        $new = $current + $value;
        $this->store[$key] = $new;

        return $new;
    }

    public function expire(string $key, int $ttl): void
    {
        $this->ttls[$key] = time() + $ttl;
    }
}
