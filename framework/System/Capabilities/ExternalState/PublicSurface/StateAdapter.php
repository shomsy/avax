<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\PublicSurface;

use Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters\MemoryStateAdapter;
use Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters\RedisStateAdapter;

interface StateAdapter
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl = 0): void;

    public function delete(string $key): void;

    public function exists(string $key): bool;

    public function increment(string $key, int $value = 1): int;

    public function expire(string $key, int $ttl): void;
}
