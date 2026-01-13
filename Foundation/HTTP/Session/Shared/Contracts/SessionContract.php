<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Contracts;

use Avax\HTTP\Session\Core\Lifecycle\SessionScope;
use Avax\HTTP\Session\Events\Events;
use Avax\HTTP\Session\Features\Flash;

/**
 * SessionContract - Formal API Contract
 *
 * Defines the public API for session management.
 *
 * Purpose:
 * - API stability for testing and DI
 * - Clear contract for implementations
 * - Documentation of public interface
 *
 * Note: This is a formal contract, not used internally.
 * SessionManager implements this implicitly.
 */
interface SessionContract
{
    /**
     * Store a value.
     *
     * @param string   $key   The key.
     * @param mixed    $value The value.
     * @param int|null $ttl   Optional TTL.
     */
    public function put(string $key, mixed $value, int|null $ttl = null) : void;

    /**
     * Retrieve a value.
     *
     * @param string $key     The key.
     * @param mixed  $default Default value.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Check if key exists.
     *
     * @param string $key The key.
     */
    public function has(string $key) : bool;

    /**
     * Remove a value.
     *
     * @param string $key The key.
     */
    public function forget(string $key) : void;

    /**
     * Get all data.
     *
     * @return array<string, mixed>
     */
    public function all() : array;

    /**
     * Clear all data.
     */
    public function flush() : void;

    /**
     * Create scoped session.
     *
     * @param string $namespace The namespace.
     *
     * @return SessionScope Scoped consumer.
     */
    public function scope(string $namespace) : SessionScope;

    /**
     * Access flash messages.
     *
     * @return \Avax\HTTP\Session\Features\Flash Flash instance.
     */
    public function flash() : Flash;

    /**
     * Access events.
     *
     * @return Events Events instance.
     */
    public function events() : Events;
}
