<?php

declare(strict_types=1);

namespace components\HTTP\Session;

use components\HTTP\Session\Events\Events;
use components\HTTP\Session\FlashSession\Flash;
use components\HTTP\Session\Shared\Contracts\SessionInterface;

/**
 * Safe no-op session implementation.
 */
final class NullSession implements SessionInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private Flash $flash;

    private Events $events;

    public function __construct()
    {
        $this->flash  = new Flash;
        $this->events = new Events;
    }

    public function set(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->put(key: $key, value: $value, ttl: $ttl);
    }

    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->data[$key] = $value;
    }

    public function delete(string $key) : void
    {
        $this->forget(key: $key);
    }

    public function forget(string $key) : void
    {
        unset($this->data[$key]);
    }

    public function remove(string $key) : void
    {
        $this->forget(key: $key);
    }

    public function all() : array
    {
        return $this->data;
    }

    public function start() : bool
    {
        return true;
    }

    public function getId() : string
    {
        return '';
    }

    public function login(string $userId, array $data = []) : void
    {
        $this->data['user']      = $userId;
        $this->data['user_data'] = $data;
    }

    public function regenerate() : void
    {
        $this->regenerateId();
    }

    public function regenerateId(bool $deleteOldSession = true) : void {}

    public function terminate(string $reason = 'logout') : void
    {
        $this->flush();
    }

    public function flush() : void
    {
        $this->data = [];
    }

    public function clear() : void
    {
        $this->flush();
    }

    public function destroy() : void
    {
        $this->flush();
    }

    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        if ($this->has(key: $key)) {
            return $this->get(key: $key);
        }

        $value = $callback();
        $this->put(key: $key, value: $value, ttl: $ttl);

        return $value;
    }

    public function has(string $key) : bool
    {
        return array_key_exists(key: $key, array: $this->data);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function flash() : Flash
    {
        return $this->flash;
    }

    public function events() : Events
    {
        return $this->events;
    }

    public function snapshot(string $name = 'default') : void {}

    public function restore(string $name = 'default') : void {}

    public function transaction(callable $callback) : void
    {
        $callback($this);
    }

    public function for(string $context) : SessionScope
    {
        return $this->scope(namespace: $context);
    }

    public function scope(string $namespace) : SessionScope
    {
        return new SessionScope(namespace: $namespace, session: $this);
    }

    public function isStarted() : bool
    {
        return true;
    }
}
