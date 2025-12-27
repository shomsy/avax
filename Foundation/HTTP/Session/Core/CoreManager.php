<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;
use Avax\HTTP\Session\Core\Lifecycle\SessionScope;
use Avax\HTTP\Session\Shared\Contracts\Security\Encrypter;

/**
 * CoreManager - Core Session Operations Orchestrator
 * ============================================================
 *
 * The CoreManager is responsible for orchestrating all fundamental
 * session operations including storage, retrieval, encryption, and
 * basic lifecycle management.
 *
 * This manager acts as the central coordinator for:
 * - Session data storage (put/get/has/forget/flush)
 * - Automatic encryption/decryption of session values
 * - TTL (time-to-live) management
 * - Namespace and context isolation
 * - Lazy evaluation (remember pattern)
 * - Policy enforcement
 *
 * Design Philosophy:
 * The CoreManager delegates actual implementation to SessionEngine
 * while providing a clean, focused API for core session operations.
 * It ensures separation of concerns by handling ONLY core functionality,
 * leaving Recovery, Audit, and Events to their respective managers.
 *
 * @package Avax\HTTP\Session\Core
 * @author  Milos
 * @version 5.0
 */
final readonly class CoreManager
{
    /**
     * CoreManager Constructor.
     *
     * @param SessionEngine $engine The core session engine.
     */
    public function __construct(private SessionEngine $engine) {}

    // ----------------------------------------------------------------
    // Core Data Operations
    // ----------------------------------------------------------------

    /**
     * Remove a specific key from the session.
     *
     * @param string $key The session key to remove.
     *
     * @return void
     */
    public function forget(string $key) : void
    {
        $this->engine->storage()->delete(key: $key);
    }

    /**
     * Retrieve all session data.
     *
     * Returns the complete session dataset as an associative array.
     * Useful for debugging, snapshots, or bulk operations.
     *
     * @return array<string, mixed> All session data.
     */
    public function all() : array
    {
        return $this->engine->storage()->all();
    }

    /**
     * Get a session consumer for a specific context.
     *
     * Contexts allow you to isolate session data by logical domain
     * (e.g., 'cart', 'user', 'preferences').
     *
     * @param string $context The context identifier.
     *
     * @return SessionScope A scoped session consumer.
     */
    public function for(string $context) : SessionScope
    {
        return $this->engine->for(context: $context);
    }

    /**
     * Get a session consumer for a specific namespace.
     *
     * Namespaces provide hierarchical isolation of session data.
     *
     * @param string $namespace The namespace identifier.
     *
     * @return SessionScope A namespaced session consumer.
     */
    public function scope(string $namespace) : SessionScope
    {
        return $this->engine->scope(namespace: $namespace);
    }

    /**
     * Get or compute a session value (lazy evaluation pattern).
     *
     * If the key exists, returns its value.
     * If not, executes the callback, stores the result with optional TTL, and returns it.
     *
     * @param string   $key      The session key.
     * @param callable $callback Function to compute the value if missing.
     * @param int|null $ttl      Optional TTL for the computed value.
     *
     * @return mixed The cached or computed value.
     */
    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        // Check if key already exists
        if ($this->has(key: $key)) {
            return $this->get(key: $key);
        }

        // Key doesn't exist - compute value via callback
        $value = $callback();

        // Store the computed value with optional TTL
        $this->put(key: $key, value: $value, ttl: $ttl);

        return $value;
    }

    /**
     * Check if a session key exists.
     *
     * @param string $key The session key to check.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key) : bool
    {
        return $this->engine->storage()->has(key: $key);
    }

    // ----------------------------------------------------------------
    // Contextual Access
    // ----------------------------------------------------------------

    /**
     * Retrieve a value from the session.
     *
     * Automatically decrypts the value if it was stored encrypted.
     *
     * @param string $key     The session key.
     * @param mixed  $default Default value if key doesn't exist.
     *
     * @return mixed The session value or default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->engine->get(key: $key, default: $default);
    }

    /**
     * Store a value in the session.
     *
     * Automatically encrypts the value if secure mode is enabled
     * in the session configuration.
     *
     * @param string   $key   The session key.
     * @param mixed    $value The value to store.
     * @param int|null $ttl   Optional time-to-live in seconds.
     *
     * @return void
     */
    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->engine->put(key: $key, value: $value, ttl: $ttl);
    }

    // ----------------------------------------------------------------
    // Lazy Evaluation & Caching
    // ----------------------------------------------------------------

    /**
     * Get the session configuration.
     *
     * @return Config The immutable configuration object.
     */
    public function config() : Config
    {
        return $this->engine->config();
    }

    // ----------------------------------------------------------------
    // Configuration & Security
    // ----------------------------------------------------------------

    /**
     * Get the encryption handler.
     *
     * @return Encrypter The active encrypter instance.
     */
    public function encrypter() : Encrypter
    {
        return $this->engine->encrypter();
    }

    // ----------------------------------------------------------------
    // Lifecycle Operations
    // ----------------------------------------------------------------

    /**
     * Regenerate the session ID.
     *
     * Critical security operation to prevent session fixation attacks.
     * Should be called on login, privilege elevation, etc.
     *
     * @return void
     */
    public function regenerate() : void
    {
        $this->engine->regenerate();
    }

    /**
     * Terminate the session.
     *
     * Securely destroys the session and clears all data.
     *
     * @param string $reason Termination reason (for audit logs).
     *
     * @return void
     */
    public function terminate(string $reason = 'logout') : void
    {
        $this->engine->terminate(reason: $reason);
    }

    /**
     * Clear all session data.
     *
     * This operation removes all keys from the session store.
     * Use with caution as this is irreversible (unless using Recovery).
     *
     * @return void
     */
    public function flush() : void
    {
        $this->engine->flush();
    }

    // ----------------------------------------------------------------
    // Internal Access
    // ----------------------------------------------------------------

    /**
     * Get the underlying SessionEngine.
     *
     * Provides access to the core engine for advanced operations.
     *
     * @return SessionEngine The session engine instance.
     */
    public function engine() : SessionEngine
    {
        return $this->engine;
    }
}
