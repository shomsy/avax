<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;
use Avax\HTTP\Session\Core\Lifecycle\SessionScope;
use Avax\HTTP\Session\Events\Events;
use Avax\HTTP\Session\Features\Flash;
use Avax\HTTP\Session\Shared\Contracts\Security\SessionIdProviderInterface;
use Avax\HTTP\Session\Shared\Contracts\SessionContract;
use Avax\HTTP\Session\Shared\Contracts\SessionInterface;

/**
 * Adapter that exposes SessionEngine through SessionInterface.
 */
final class SessionAdapter implements SessionInterface, SessionContract
{
    private Flash  $flash;
    private Events $events;

    public function __construct(
        private readonly SessionEngine              $engine,
        private readonly SessionIdProviderInterface $idProvider,
        Flash|null                                  $flash = null,
        Events|null                                 $events = null
    )
    {
        $this->flash  = $flash ?? new Flash();
        $this->events = $events ?? $this->engine->events() ?? new Events();
    }

    public function events() : Events
    {
        return $this->events;
    }

    public function set(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->put(key: $key, value: $value, ttl: $ttl);
    }

    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->engine->put(key: $key, value: $value, ttl: $ttl);
    }

    public function remove(string $key) : void
    {
        $this->forget(key: $key);
    }

    public function forget(string $key) : void
    {
        $this->engine->storage()->delete(key: $key);
    }

    public function delete(string $key) : void
    {
        $this->forget(key: $key);
    }

    public function all() : array
    {
        return $this->engine->storage()->all();
    }

    public function flush() : void
    {
        $this->engine->flush();
    }

    public function start() : bool
    {
        if ($this->idProvider->isActive()) {
            return true;
        }

        $this->idProvider->generate();

        return true;
    }

    public function regenerateId(bool $deleteOldSession = true) : void
    {
        if (! $deleteOldSession) {
            $this->idProvider->regenerate(deleteOld: false);

            return;
        }

        $this->engine->regenerate();
    }

    public function getId() : string
    {
        return $this->idProvider->current();
    }

    public function login(string $userId) : void
    {
        $this->engine->login(userId: $userId, data: []);
    }

    public function terminate(string $reason = 'logout') : void
    {
        $this->engine->terminate(reason: $reason);
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
        return $this->engine->storage()->has(key: $key);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->engine->get(key: $key, default: $default);
    }

    public function scope(string $namespace) : SessionScope
    {
        return $this->engine->scope(namespace: $namespace);
    }

    public function flash() : Flash
    {
        return $this->flash;
    }
}
