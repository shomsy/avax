<?php

declare(strict_types=1);

namespace Avax\Components\Infrastructure\System\Capabilities\Cache;

/**
 * Low-level driver interface to abstract away specific storage details.
 */
interface Driver
{
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function get(string $key): mixed;

    public function del(string $key): int;

    /**
     * @param  array<string, mixed>  $dictionary
     */
    public function hMSet(string $key, array $dictionary): bool;

    /**
     * @return array<string, mixed>
     */
    public function hGetAll(string $key): array;

    public function expire(string $key, int $seconds): bool;

    public function sAdd(string $key, string $value): int;

    public function sRem(string $key, string $value): int;

    /**
     * @return list<string>
     */
    public function sMembers(string $key): array;
}
