<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Contracts\Storage;

interface StoreInterface
{
    public function get(string $key, mixed $default = null) : mixed;

    public function put(string $key, mixed $value, int|null $ttl = null) : void;

    public function has(string $key) : bool;

    public function delete(string $key) : void;

    public function all() : array;

    public function flush() : void;

    public function flushNamespace(string $prefix) : void;
}
