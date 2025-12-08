<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Features\Events;
use Avax\HTTP\Session\Features\Flash;
use Avax\HTTP\Session\Storage\ArrayStore;

/**
 * NullSession
 *
 * Safe no-op implementation used as a fallback when no real session is available.
 */
final class NullSession implements SessionInterface
{
    public function put(string $key, mixed $value, int|null $ttl = null) : void {}

    public function set(string $key, mixed $value, int|null $ttl = null) : void {}

    public function get(string $key, mixed $default = null) : mixed
    {
        return $default;
    }

    public function has(string $key) : bool
    {
        return false;
    }

    public function forget(string $key) : void {}

    public function delete(string $key) : void {}

    public function remove(string $key) : void {}

    public function all() : array
    {
        return [];
    }

    public function flush() : void {}

    public function start() : bool
    {
        return false;
    }

    public function regenerateId(bool $deleteOldSession = true) : void {}

    public function getId() : string
    {
        return '';
    }

    public function login(string $userId) : void {}

    public function terminate(string $reason = 'logout') : void {}

    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        return $callback();
    }

    public function flash() : Flash
    {
        // Safe default flash that uses in-memory store
        return new Flash(new ArrayStore());
    }

    public function events() : Events
    {
        return new Events();
    }
}
