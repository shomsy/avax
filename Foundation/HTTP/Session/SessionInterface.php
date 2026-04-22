<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

interface SessionInterface
{
    public function put(string $key, mixed $value, int|null $ttl = null) : void;
    public function get(string $key, mixed $default = null) : mixed;
    public function has(string $key) : bool;
    public function forget(string $key) : void;
    public function delete(string $key) : void;
    public function all() : array;
    public function flush() : void;
    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed;
    public function regenerate() : void;
    public function login(string $userId, array $data = []) : void;
    public function terminate(string $reason = 'logout') : void;
    public function snapshot(string $name = 'default') : void;
    public function restore(string $name = 'default') : void;
    public function transaction(callable $callback) : void;
    public function scope(string $namespace) : SessionScope;
    public function for(string $context) : SessionScope;
}
