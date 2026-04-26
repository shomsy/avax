<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

interface CacheContract extends CacheInterface
{
    public function get(string $key, mixed $default = null) : mixed;

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool;

    public function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed;

    public function delete(string $key) : bool;

    public function clear() : bool;

    public function has(string $key) : bool;

    public function getMultiple(iterable $keys, mixed $default = null) : iterable;

    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null) : bool;

    public function deleteMultiple(iterable $keys) : bool;
}