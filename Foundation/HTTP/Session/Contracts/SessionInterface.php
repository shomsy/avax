<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

use Avax\HTTP\Session\Features\Events;
use Avax\HTTP\Session\Features\Flash;

/**
 * SessionInterface
 *
 * Unified contract for the refactored session component.
 * Provides a small, expressive API used across HTTP, Auth and Security layers.
 */
interface SessionInterface
{
    public function put(string $key, mixed $value, int|null $ttl = null) : void;

    /**
     * Alias for put().
     */
    public function set(string $key, mixed $value, int|null $ttl = null) : void;

    public function get(string $key, mixed $default = null) : mixed;

    public function has(string $key) : bool;

    public function forget(string $key) : void;

    /**
     * Alias for forget().
     */
    public function delete(string $key) : void;

    /**
     * Alias for forget().
     */
    public function remove(string $key) : void;

    public function all() : array;

    public function flush() : void;

    /**
     * Start the underlying session mechanism (idempotent).
     */
    public function start() : bool;

    public function regenerateId(bool $deleteOldSession = true) : void;

    public function getId() : string;

    public function login(string $userId) : void;

    public function terminate(string $reason = 'logout') : void;

    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed;

    public function flash() : Flash;

    public function events() : Events;
}
