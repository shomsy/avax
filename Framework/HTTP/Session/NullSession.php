<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session;

use Closure;
use Gemini\HTTP\Session\Contracts\BagRegistryInterface;
use Gemini\HTTP\Session\Contracts\SessionInterface;

/**
 * NullSession provides a no-op implementation of the SessionInterface.
 *
 * Useful for testing, stateless APIs, or fallback scenarios where
 * session state is intentionally disabled.
 */
final class NullSession implements SessionInterface
{
    public function get(string $key, mixed $default = null) : mixed
    {
        return $default;
    }

    public function set(string $key, mixed $value) : void {}

    public function has(string $key) : bool
    {
        return false;
    }

    public function remove(string $key) : void {}

    public function all() : array
    {
        return [];
    }

    public function start() : void {}

    public function delete(string $key) : void {}

    public function flash(string $key, mixed $value) : void {}

    public function getFlash(string $key, mixed $default = null) : mixed
    {
        return $default;
    }

    public function keepFlash(string $key) : void {}

    public function flashInput(array $input) : void {}

    public function getOldInput(string $key, mixed $default = null) : mixed
    {
        return $default;
    }

    public function flush() : void {}

    public function invalidate() : void {}

    public function regenerateId(bool $deleteOldSession = true) : void {}

    public function offsetExists(mixed $offset) : bool
    {
        return false;
    }

    public function offsetGet(mixed $offset) : mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value) : void {}

    public function offsetUnset(mixed $offset) : void {}

    public function put(string $key, mixed $value) : void {}

    public function putWithTTL(string $key, mixed $value, int $ttl) : void {}

    public function pull(string $key, mixed $default = null) : mixed
    {
        return $default;
    }

    public function remember(string $key, Closure $callback) : mixed
    {
        return $callback();
    }

    public function increment(string $key, int $amount = 1) : int
    {
        return $amount;
    }

    public function decrement(string $key, int $amount = 1) : int
    {
        return -$amount;
    }

    public function getRegistry() : BagRegistryInterface
    {
        return app(BagRegistryInterface::class);
    }
}
