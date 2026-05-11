<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\Keys;

interface IdempotencyStore
{
    public function get(string $key) : array|null;

    public function set(string $key, array $value, int $ttl): void;

    public function has(string $key): bool;
}
