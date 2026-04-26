<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Audit\Audit;
use Avax\HTTP\Session\Events\Events;
use Avax\HTTP\Session\Recovery\Recovery;
use Avax\HTTP\Session\SessionStore\SessionStore;

/**
 * Lightweight Session facade — delegates to concrete capabilities.
 * This replaces manager-heavy ownership with direct capability usage.
 */
final class Session implements SessionInterface
{
    private SessionStore  $store;
    private Audit|null    $audit;
    private Events        $events;
    private Recovery|null $recovery;
    private string        $sessionId;

    public function __construct(
        SessionStore  $store,
        Audit|null    $audit = null,
        Events|null   $events = null,
        Recovery|null $recovery = null
    )
    {
        $this->store     = $store;
        $this->audit     = $audit;
        $this->events    = $events ?? new Events();
        $this->recovery  = $recovery;
        $this->sessionId = session_id() !== '' ? session_id() : uniqid(prefix: 'session_', more_entropy: true);
    }

    public function set(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->put(key: $key, value: $value, ttl: $ttl);
    }

    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->store->put(key: $key, value: $value, ttl: $ttl);
        $this->audit?->record(event: 'session.put', data: ['key' => $key]);
        $this->events->dispatch(event: 'session.stored', data: ['key' => $key]);
    }

    public function remove(string $key) : void
    {
        $this->forget(key: $key);
    }

    public function forget(string $key) : void
    {
        $this->store->delete(key: $key);
        $this->audit?->record(event: 'session.forget', data: ['key' => $key]);
        $this->events->dispatch(event: 'session.deleted', data: ['key' => $key]);
    }

    public function delete(string $key) : void
    {
        $this->forget(key: $key);
    }

    public function all() : array
    {
        return $this->store->all();
    }

    public function clear() : void
    {
        $this->flush();
    }

    public function flush() : void
    {
        $this->store->flush();
        $this->audit?->record(event: 'session.flush');
        $this->events->dispatch(event: 'session.flushed');
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
        return $this->store->has(key: $key);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $value = $this->store->get(key: $key, default: $default);
        $this->events->dispatch(event: 'session.retrieved', data: ['key' => $key]);

        return $value;
    }

    // Minimal session lifecycle helpers — best-effort implementations

    public function regenerateId(bool $deleteOldSession = true) : void
    {
        $this->regenerate();
    }

    public function regenerate() : void
    {
        $this->sessionId = uniqid(prefix: 'session_', more_entropy: true);
        $this->audit?->record(event: 'session.regenerated');
    }

    public function getId() : string
    {
        return $this->sessionId;
    }

    public function start() : bool
    {
        return true;
    }

    public function isStarted() : bool
    {
        return true;
    }

    public function login(string $userId, array $data = []) : void
    {
        $this->put(key: 'user_id', value: $userId);
        $this->put(key: 'user_data', value: $data);
        $this->audit?->record(event: 'session.login', data: ['user_id' => $userId]);
        $this->events->dispatch(event: 'session.login', data: ['user_id' => $userId]);
    }

    public function destroy() : void
    {
        $this->terminate(reason: 'destroy');
    }

    public function terminate(string $reason = 'logout') : void
    {
        $userId = $this->store->get(key: 'user_id');
        $this->audit?->record(event: 'session.terminated', data: ['user_id' => $userId, 'reason' => $reason]);
        $this->events->dispatch(event: 'session.terminated', data: ['user_id' => $userId, 'reason' => $reason]);
        $this->flush();
    }

    // Recovery helpers

    public function snapshot(string $name = 'default') : void
    {
        $this->recovery?->backup(name: $name);
    }

    public function restore(string $name = 'default') : void
    {
        $this->recovery?->restore(name: $name);
    }

    public function transaction(callable $callback) : void
    {
        if ($this->recovery !== null) {
            $this->recovery->transaction(operation: $callback);

            return;
        }

        // Fallback: execute callback directly
        $callback($this);
    }

    // Simple scope implementation

    public function for(string $context) : SessionScope
    {
        return $this->scope(namespace: $context);
    }

    public function scope(string $namespace) : SessionScope
    {
        return new SessionScope(namespace: $namespace, session: $this);
    }
}
