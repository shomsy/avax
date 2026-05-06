<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

use DateInterval;

interface CacheContract
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool;

    public function remember(string $key, int|DateInterval|null $ttl, callable $loader): mixed;

    public function delete(string $key): bool;

    public function clear(): bool;

    public function has(string $key): bool;

    /**
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * @param  iterable<string, mixed>  $values
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool;

    /**
     * @param  iterable<string>  $keys
     */
    public function deleteMultiple(iterable $keys): bool;
}
