<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Audit\AuditManager;
use Avax\HTTP\Session\Core\CoreManager;
use Avax\HTTP\Session\Core\Lifecycle\SessionScope;
use Avax\HTTP\Session\Events\EventsManager;
use Avax\HTTP\Session\Recovery\RecoveryManager;
use Avax\HTTP\Session\Shared\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Shared\Security\SessionNonce;
use Avax\HTTP\Session\Shared\Security\SessionRegistry;
use LogicException;
use Throwable;

/**
 * 🧠 Session - Unified Enterprise Facade
 * ============================================================
 *
 * The all-in-one orchestrator for Avax's session engine.
 *
 * This class unifies multiple session subsystems into a single,
 * developer-friendly API — covering:
 *
 * - **Core Operations**: Secure data storage (encrypted, signed)
 * - **TTL & Namespacing**: Time-to-live and namespace isolation
 * - **Flash Messages**: Temporary session data
 * - **Event Bus**: Integration with event-driven architecture
 * - **Auditing**: PSR-3 compliant logging and compliance tracking
 * - **Recovery**: Snapshots, rollback, and transactional operations
 * - **Multi-Device**: Registry tracking for concurrent sessions
 * - **Security**: Policies, nonce-based replay protection
 *
 * 💡 **Architecture**:
 * This facade delegates to four domain managers:
 * - `CoreManager`: Handles all core session operations
 * - `RecoveryManager`: Manages snapshots and transactions
 * - `AuditManager`: Provides audit logging and compliance
 * - `EventsManager`: Coordinates event dispatching
 *
 * Each manager is responsible for its own domain, ensuring clean
 * separation of concerns and modular architecture.
 *
 * @package Avax\HTTP\Session
 * @author  Milos
 * @version 5.0
 */
final readonly class Session
{
    /**
     * Session Constructor.
     *
     * @param CoreManager     $core     Core session operations manager.
     * @param RecoveryManager $recovery Recovery operations manager.
     * @param AuditManager    $audit    Audit logging manager.
     * @param EventsManager   $events   Event dispatching manager.
     */
    public function __construct(
        private CoreManager     $core,
        private RecoveryManager $recovery,
        private AuditManager    $audit,
        private EventsManager   $events
    ) {}

    // ----------------------------------------------------------------
    // 🧱 Core Data API
    // ----------------------------------------------------------------

    /**
     * Store a value in the session.
     *
     * Automatically encrypts the value if secure mode is enabled.
     * Triggers audit logging and event dispatching.
     *
     * @param string   $key   The session key.
     * @param mixed    $value The value to store.
     * @param int|null $ttl   Optional time-to-live in seconds.
     *
     * @return void
     */
    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->core->put(key: $key, value: $value, ttl: $ttl);
        $this->audit->record(event: 'session.put', data: compact(var_name: 'key'));
        $this->events->dispatch(event: 'session.stored', data: compact(var_name: 'key'));
    }

    /**
     * Retrieve a value from the session.
     *
     * Automatically decrypts the value if it was stored encrypted.
     * Triggers event dispatching for retrieval tracking.
     *
     * @param string $key     The session key.
     * @param mixed  $default Default value if key doesn't exist.
     *
     * @return mixed The session value or default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $value = $this->core->get(key: $key, default: $default);
        $this->events->dispatch(event: 'session.retrieved', data: compact(var_name: 'key'));

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
        return $this->core->has(key: $key);
    }

    /**
     * Remove a specific key from the session.
     *
     * Triggers audit logging and event dispatching.
     *
     * @param string $key The session key to remove.
     *
     * @return void
     */
    public function forget(string $key) : void
    {
        $this->core->forget(key: $key);
        $this->audit->record(event: 'session.forget', data: compact(var_name: 'key'));
        $this->events->dispatch(event: 'session.deleted', data: compact(var_name: 'key'));
    }

    /**
     * Clear all session data.
     *
     * This operation removes all keys from the session store.
     * Triggers audit logging and event dispatching.
     *
     * @return void
     */
    public function flush() : void
    {
        $this->core->flush();
        $this->audit->record(event: 'session.flush');
        $this->events->dispatch(event: 'session.flushed');
    }

    /**
     * Get a session consumer for a specific context.
     *
     * **Purpose**: Isolate session data by logical domain or feature.
     *
     * **Use Case**: When you need to separate data by business context
     * (e.g., 'cart', 'checkout', 'wizard', 'admin').
     *
     * **Example**:
     * ```php
     * $session->for('cart')->put('items', $items);
     * $session->for('checkout')->put('step', 2);
     * ```
     *
     * **Difference from scope()**:
     * - `for()` is for **flat, context-based** isolation
     * - `scope()` is for **hierarchical, namespace-based** isolation
     *
     * @param string $context The context identifier (e.g., 'cart', 'wizard').
     *
     * @return SessionScope A scoped session consumer.
     */
    public function for(string $context) : SessionScope
    {
        return $this->core->for(context: $context);
    }

    // ----------------------------------------------------------------
    // 🧠 Contextual Consumers
    // ----------------------------------------------------------------

    /**
     * Get a session consumer for a specific namespace.
     *
     * **Purpose**: Isolate session data using hierarchical namespaces.
     *
     * **Use Case**: When you need nested, hierarchical data organization
     * (e.g., 'user.preferences', 'app.settings.theme').
     *
     * **Example**:
     * ```php
     * $session->scope('user.preferences')->put('theme', 'dark');
     * $session->scope('app.settings')->put('locale', 'en');
     * ```
     *
     * **Difference from for()**:
     * - `scope()` supports **dot notation** for hierarchical namespaces
     * - `for()` is for **flat contexts** without hierarchy
     *
     * **Note**: Both methods return the same `SessionScope` object.
     * The distinction is semantic and helps with code readability.
     *
     * @param string $namespace The namespace identifier (supports dot notation).
     *
     * @return SessionScope A namespaced session consumer.
     */
    public function scope(string $namespace) : SessionScope
    {
        return $this->core->scope(namespace: $namespace);
    }

    /**
     * Get or compute a session value (lazy evaluation pattern).
     *
     * **How it works:**
     * 1. If the key exists → returns its value immediately
     * 2. If not → executes callback, stores result with optional TTL, returns it
     *
     * **Example:**
     * ```php
     * $user = $session->remember('user_profile', function() {
     *     return Database::query('SELECT * FROM users WHERE id = ?', [123]);
     * }, ttl: 3600); // Cache for 1 hour
     * ```
     *
     * @param string   $key      The session key.
     * @param callable $callback Function to compute the value if missing.
     * @param int|null $ttl      Optional TTL for the computed value.
     *
     * @return mixed The cached or computed value.
     */
    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        return $this->core->remember(key: $key, callback: $callback, ttl: $ttl);
    }

    // ----------------------------------------------------------------
    // Security Policy Management
    // ----------------------------------------------------------------

    /**
     * Register a security policy.
     *
     * **IMPORTANT:** Policies cannot be altered at runtime in this architecture.
     * All security policies must be configured via DI in SessionSecurityProvider.
     *
     * This method exists for API compatibility but will throw an exception.
     *
     * @param PolicyInterface $policy The policy to register.
     *
     * @return self Fluent interface.
     * @throws \LogicException Always throws - policies are immutable.
     */
    public function registerPolicy(PolicyInterface $policy) : self
    {
        throw new LogicException(
            message: 'Session policies cannot be altered at runtime. ' .
            'Configure all security policies via DI in SessionSecurityProvider.'
        );
    }

    // ----------------------------------------------------------------
    // ⚖️ Policies & Security
    // ----------------------------------------------------------------

    /**
     * Create a snapshot of the current session state.
     *
     * @param string $name Snapshot identifier (default: 'default').
     *
     * @return void
     */
    public function snapshot(string $name = 'default') : void
    {
        $this->recovery->snapshot(name: $name);
    }

    // ----------------------------------------------------------------
    // 💾 Recovery API
    // ----------------------------------------------------------------

    /**
     * Restore session state from a named snapshot.
     *
     * @param string $name Snapshot identifier (default: 'default').
     *
     * @return void
     */
    public function restore(string $name = 'default') : void
    {
        $this->recovery->restore(name: $name);
    }

    /**
     * Execute a callback within a transactional context.
     *
     * Automatically begins a transaction, executes the callback,
     * and commits on success or rolls back on failure.
     *
     * @param callable $callback The operation to execute.
     *
     * @return void
     *
     * @throws Throwable Re-throws the original exception after rollback.
     */
    public function transaction(callable $callback) : void
    {
        $this->recovery->transaction(callback: function () use ($callback) {
            $callback($this);
        });
    }

    /**
     * Regenerate the session ID.
     *
     * Critical security operation to prevent session fixation attacks.
     * Should be called on login, privilege elevation, etc.
     *
     * @return void
     * @throws \Random\RandomException
     */
    public function regenerate() : void
    {
        $this->core->regenerate();
        $this->audit->record(event: 'session.regenerated');
    }

    // ----------------------------------------------------------------
    // ⚙️ Session Lifecycle
    // ----------------------------------------------------------------

    /**
     * Perform login operation.
     *
     * Stores user authentication data and regenerates session ID.
     *
     * @param string $userId User identifier.
     * @param array  $data   Additional user data to store.
     *
     * @return void
     */
    public function login(string $userId, array $data = []) : void
    {
        $this->core->engine()->login(userId: $userId, data: $data);
        $this->audit->record(event: 'session.login', data: compact(var_name: 'userId'));
        $this->events->dispatch(event: 'session.login', data: compact(var_name: 'userId'));
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
        $this->core->terminate(reason: $reason);
        $this->audit->record(event: 'session.terminated', data: compact(var_name: 'reason'));
        $this->events->dispatch(event: 'session.terminated', data: compact(var_name: 'reason'));
    }

    /**
     * Get the session registry for multi-device tracking.
     *
     * @return SessionRegistry|null The session registry or null.
     */
    public function getRegistry() : SessionRegistry|null
    {
        return $this->core->engine()->registry();
    }

    // ----------------------------------------------------------------
    // 🔐 Security Subsystems
    // ----------------------------------------------------------------

    /**
     * Get the session nonce for replay protection.
     *
     * @return SessionNonce|null The session nonce or null.
     */
    public function nonce() : SessionNonce|null
    {
        return $this->core->engine()->nonce();
    }

    /**
     * Access the Core domain manager.
     *
     * Provides direct access to core session operations.
     *
     * @return CoreManager The core manager instance.
     */
    public function core() : CoreManager
    {
        return $this->core;
    }

    // ----------------------------------------------------------------
    // 🪄 Fluent Domain Accessors
    // ----------------------------------------------------------------

    /**
     * Access the Recovery domain manager.
     *
     * Provides direct access to recovery operations.
     *
     * @return RecoveryManager The recovery manager instance.
     */
    public function recovery() : RecoveryManager
    {
        return $this->recovery;
    }

    /**
     * Access the Audit domain manager.
     *
     * Provides direct access to audit logging.
     *
     * @return AuditManager The audit manager instance.
     */
    public function audit() : AuditManager
    {
        return $this->audit;
    }

    /**
     * Access the Events domain manager.
     *
     * Provides direct access to event dispatching.
     *
     * @return EventsManager The events manager instance.
     */
    public function events() : EventsManager
    {
        return $this->events;
    }

    /**
     * Get session statistics and diagnostics.
     *
     * @return array<string, mixed> Session statistics.
     */
    public function stats() : array
    {
        return [
            'total_keys'     => count(value: $this->all()),
            'audit_enabled'  => $this->audit->isEnabled(),
            'events_enabled' => $this->events->isEnabled(),
        ];
    }

    // ----------------------------------------------------------------
    // 🩺 Diagnostics
    // ----------------------------------------------------------------

    /**
     * Retrieve all session data.
     *
     * @return array<string, mixed> All session data.
     */
    public function all() : array
    {
        return $this->core->all();
    }
}
