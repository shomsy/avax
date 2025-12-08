<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

use Avax\HTTP\Session\Providers\SessionConsumer;

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
 *
 * @package Avax\HTTP\Session
 */
interface SessionContract
{
    /**
     * Store a value.
     *
     * @param string   $key   The key.
     * @param mixed    $value The value.
     * @param int|null $ttl   Optional TTL.
     *
     * @return void
     */
    public function put(string $key, mixed $value, ?int $ttl = null): void;

    /**
     * Retrieve a value.
     *
     * @param string $key     The key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if key exists.
     *
     * @param string $key The key.
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove a value.
     *
     * @param string $key The key.
     *
     * @return void
     */
    public function forget(string $key): void;

    /**
     * Get all data.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Clear all data.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Create scoped session.
     *
     * @param string $namespace The namespace.
     *
     * @return SessionConsumer Scoped consumer.
     */
    public function scope(string $namespace): SessionConsumer;

    /**
     * Access flash messages.
     *
     * @return \Avax\HTTP\Session\Features\Flash Flash instance.
     */
    public function flash(): \Avax\HTTP\Session\Features\Flash;

    /**
     * Access events.
     *
     * @return \Avax\HTTP\Session\Features\Events Events instance.
     */
    public function events(): \Avax\HTTP\Session\Features\Events;
}
