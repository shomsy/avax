<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Lifecycle;

use Avax\HTTP\Context\HttpContextInterface;
use Avax\HTTP\Session\Audit\Audit;
use Avax\HTTP\Session\Core\Config;
use Avax\HTTP\Session\Core\Lifecycle;
use Avax\HTTP\Session\Events\Events;
use Avax\HTTP\Session\Recovery\Recovery;
use Avax\HTTP\Session\Shared\Contracts\Security\Encrypter;
use Avax\HTTP\Session\Shared\Contracts\Security\SessionIdProviderInterface;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;
use Avax\HTTP\Session\Shared\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Shared\Security\SessionNonce;
use Avax\HTTP\Session\Shared\Security\SessionRegistry;
use Avax\HTTP\Session\Shared\Security\SessionSignature;
use Throwable;

/**
 * Class SessionEngine
 * ============================================================
 * 🧠 Core Session Lifecycle Coordinator
 *
 * The central “kernel” of the Avax session system.
 * It connects persistent storage, encryption, recovery,
 * security policies, and observability features into one cohesive unit.
 *
 * Responsibilities:
 *  - Manages persistent session storage (file, Redis, etc.)
 *  - Encrypts/decrypts session values via Encrypter
 *  - Provides Recovery safety (snapshots, rollback, transactions)
 *  - Enforces policies (max idle, lifetime, secure-only, etc.)
 *  - Maintains session registry (multi-login & concurrency)
 *  - Applies signature integrity via SessionSignature
 *  - Integrates PSR-3 observability features (Audit, Metrics, etc.)
 *
 * 💬 Think of it as the “session OS kernel” — everything passes through it.
 *
 * @package Avax\HTTP\Session\Core
 */
final readonly class SessionEngine
{
    /**
     * SessionEngine Constructor.
     *
     * All dependencies injected via DI - no runtime registration.
     * Policies and Features are immutable after construction.
     *
     * @param StoreInterface             $store      Persistent session store driver.
     * @param Config                     $config     Immutable configuration object.
     * @param Encrypter                  $encrypter  Encryption engine for secure payloads.
     * @param Recovery                   $recovery   Recovery manager for backup and rollback.
     * @param SessionIdProviderInterface $idProvider Session ID generation and management.
     * @param Audit|null                 $audit      Audit feature for logging.
     * @param Events|null                $events     Events feature for dispatching.
     * @param SessionSignature|null      $signature  Optional integrity validator for session IDs.
     * @param PolicyInterface|null       $policies   Optional policy group or composite.
     * @param SessionRegistry|null       $registry   Optional multi-login registry handler.
     */
    public function __construct(
        private StoreInterface             $store,
        private Config                     $config,
        private Encrypter                  $encrypter,
        private Recovery                   $recovery,
        private SessionIdProviderInterface $idProvider,
        private Audit|null                 $audit = null,
        private Events|null                $events = null,
        private SessionSignature|null      $signature = null,
        private PolicyInterface|null       $policies = null,
        private SessionRegistry|null       $registry = null,
        private HttpContextInterface|null  $httpContext = null
    ) {}

    // ---------------------------------------------------------------------
    // 🔹 Core Accessors
    // ---------------------------------------------------------------------

    /**
     * Get the session data store.
     */
    public function storage() : StoreInterface
    {
        return $this->store;
    }

    /**
     * Get the immutable configuration for this session.
     */
    public function config() : Config
    {
        return $this->config;
    }

    /**
     * Get the active encryption handler.
     */
    public function encrypter() : Encrypter
    {
        return $this->encrypter;
    }

    /**
     * Get the session recovery manager (snapshots, rollback, transactions).
     */
    public function recovery() : Recovery
    {
        return $this->recovery;
    }

    /**
     * Get the cryptographic session signature validator, if configured.
     */
    public function signature() : SessionSignature|null
    {
        return $this->signature;
    }

    /**
     * Get the currently active session policy or composite policy group.
     */
    public function policies() : PolicyInterface|null
    {
        return $this->policies;
    }

    /**
     * Get the session registry for tracking user sessions (multi-login control).
     */
    public function registry() : SessionRegistry|null
    {
        return $this->registry;
    }

    // ---------------------------------------------------------------------
    // 🔹 Feature Accessors (Immutable - Injected via DI)
    // ---------------------------------------------------------------------

    /**
     * Restore the session to the last valid snapshot.
     */
    public function restore() : void
    {
        $this->recovery->restore();
        $this->audit()?->record(event: 'session_restored', data: ['time' => time()]);
    }

    /**
     * Get the Audit feature (injected via DI).
     *
     * @return Audit|null The audit feature or null.
     */
    public function audit() : Audit|null
    {
        return $this->audit;
    }

    // ---------------------------------------------------------------------
    // 🔹 Recovery Operations
    // ---------------------------------------------------------------------

    /**
     * Execute a callback within a transactional session context.
     * If an exception occurs, the transaction is automatically rolled back.
     *
     * @param callable $callback The function to execute atomically.
     *
     * @throws Throwable Re-throws the original exception if the transaction fails.
     */
    public function transactional(callable $callback) : void
    {
        try {
            $this->recovery->beginTransaction();
            $callback($this);
            $this->recovery->commit();

            $this->audit()?->record(event: 'transaction_committed');
        } catch (Throwable $e) {
            $this->recovery->rollback();
            $this->audit()?->record(event: 'transaction_rolled_back', data: ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a session consumer for a specific context.
     *
     * Contexts allow you to isolate session data by logical domain.
     *
     * @param string $context The context identifier.
     *
     * @return \Avax\HTTP\Session\Core\Lifecycle\SessionScope A scoped session consumer.
     */
    public function for(string $context) : Lifecycle\SessionScope
    {
        return new Lifecycle\SessionScope(namespace: $context, engine: $this);
    }

    /**
     * Create a session consumer for a specific namespace.
     *
     * Namespaces provide hierarchical isolation of session data.
     *
     * @param string $namespace The namespace identifier.
     *
     * @return \Avax\HTTP\Session\Core\Lifecycle\SessionScope A namespaced session consumer.
     */
    public function scope(string $namespace) : Lifecycle\SessionScope
    {
        return new Lifecycle\SessionScope(namespace: $namespace, engine: $this);
    }

    // ---------------------------------------------------------------------
    // 🔹 Recovery Convenience Wrappers
    // ---------------------------------------------------------------------

    /**
     * Get the session nonce for replay protection.
     *
     * @return SessionNonce|null The session nonce or null.
     */
    public function nonce() : SessionNonce|null
    {
        // Create a SessionNonce instance using the store
        return new SessionNonce(store: $this->store);
    }

    // ---------------------------------------------------------------------
    // 🔹 Policy Enforcement
    // ---------------------------------------------------------------------

    /**
     * Perform user login operation.
     *
     * This is a high-level convenience method that:
     * 1. Regenerates the session ID (prevents session fixation)
     * 2. Stores user authentication data
     * 3. Registers the session in the multi-device registry
     * 4. Records audit trail
     * 5. Dispatches login events
     *
     * Enterprise Features:
     * - Automatic session ID regeneration
     * - Multi-device session tracking
     * - Comprehensive audit logging
     * - Event-driven architecture integration
     * - IP address and user agent tracking
     *
     * @param string $userId User identifier (username, email, or UUID).
     * @param array  $data   Additional user data to store in session.
     *
     * @return void
     */
    public function login(string $userId, array $data = []) : void
    {
        // Step 1: Enforce security policies
        $this->policies?->enforce(data: $this->buildContext(userId: $userId));

        // Step 2: Regenerate session ID for security
        $this->regenerate(userId: $userId);

        // Step 2: Store user authentication data
        $this->put(key: 'user_id', value: $userId, userId: $userId);
        $this->put(key: 'user_data', value: $data, userId: $userId);
        $this->put(key: 'logged_in_at', value: time(), userId: $userId);
        $this->put(key: 'last_activity', value: time(), userId: $userId);

        // Step 3: Store security metadata
        $clientIp  = $this->resolveClientIp();
        $userAgent = $this->resolveUserAgent();

        $this->put(key: 'ip_address', value: $clientIp, userId: $userId);
        $this->put(key: 'user_agent', value: $userAgent);

        // Step 4: Register in multi-device registry (if available)
        $this->registry?->register(
            userId   : $userId,
            sessionId: session_id(),
            metadata : [
                'ip'         => $clientIp,
                'user_agent' => $userAgent,
                'login_time' => time()
            ]
        );

        // Step 5: Audit logging
        $this->audit()?->record(
            event: 'user_login',
            data : [
                'user_id'   => $userId,
                'timestamp' => time(),
                'ip'        => $clientIp
            ]
        );

        // Step 6: Dispatch login event
        $this->events()?->dispatch(
            event: 'session.login',
            data : compact(var_name: 'userId')
        );
    }

    /**
     * Build session context for policy evaluation.
     *
     * Creates a comprehensive context array containing all relevant
     * session metadata for security policy enforcement.
     *
     * @return array<string, mixed> Session context data.
     */
    /**
     * Build session context for policy evaluation.
     *
     * Creates a comprehensive context array containing all relevant
     * session metadata for security policy enforcement.
     *
     * @param string|null $userId Optional user ID to override store lookup.
     *
     * @return array<string, mixed> Session context data.
     */
    private function buildContext(string|null $userId = null) : array
    {
        $clientIp  = $this->resolveClientIp();
        $userAgent = $this->resolveUserAgent();

        return [
            'session_id'    => $this->idProvider->current(),
            'ip'            => $clientIp,
            'client_ip'     => $clientIp,
            'user_agent'    => $userAgent,
            'is_secure'     => $this->httpContext?->isSecure() ?? false,
            'last_activity' => time(),
            'created_at'    => $this->store->get(key: '_created_at', default: time()),
            'user_id'       => $userId ?? $this->store->get(key: 'user_id'),
            '_client_ip'    => $this->store->get(key: 'ip_address')
        ];
    }

    private function resolveClientIp() : string
    {
        $ip = $this->httpContext?->clientIp();
        if ($ip === null || $ip === '') {
            return 'unknown';
        }

        return $ip;
    }

    private function resolveUserAgent() : string
    {
        $agent = $this->httpContext?->userAgent();
        if ($agent === null || $agent === '') {
            return 'unknown';
        }

        return $agent;
    }

    /**
     * Retrieve a value from the session store (auto-decrypted if necessary).
     *
     * @param string $key     Session key.
     * @param mixed  $default Default value if key does not exist.
     *
     * @return mixed The session value or the default.
     */
    public function get(string $key, mixed $default = null, string|null $userId = null) : mixed
    {
        // Enforce security policies
        $this->policies?->enforce(data: $this->buildContext(userId: $userId));

        // Update registry activity
        $userId ??= $this->store->get(key: 'user_id');

        if ($userId) {
            $this->registry?->updateActivity(
                userId   : $userId,
                sessionId: $this->idProvider->current()
            );
        }

        $data = $this->store->get(key: $key, default: $default);

        if ($data === null) {
            return $default;
        }

        if ($this->config->secure && is_string(value: $data)) {
            $data = $this->encrypter->decrypt(encrypted: $data);
        }

        $this->audit()?->record(event: 'get', data: compact(var_name: 'key'));

        return $data;
    }

    // ---------------------------------------------------------------------
    // 🔹 Contextual Session Consumers
    // ---------------------------------------------------------------------

    /**
     * Regenerate the session ID.
     *
     * Critical security operation to prevent session fixation attacks.
     * Uses SessionIdProvider for proper ID regeneration.
     *
     * @return void
     */
    public function regenerate(string|null $userId = null) : void
    {
        // Enforce security policies before regeneration
        $this->policies?->enforce(data: $this->buildContext(userId: $userId));

        // Get old ID before regeneration
        $oldId = $this->idProvider->current();

        // Regenerate using provider (handles native PHP session or custom)
        $newId = $this->idProvider->regenerate();

        // Audit the regeneration
        $this->audit()?->record(
            event: 'session.lifecycle.regenerate',
            data : compact('oldId', 'newId')
        );

        // Dispatch event
        $this->events()?->dispatch(
            event: 'session.lifecycle.regenerate',
            data : compact('oldId', 'newId')
        );
    }

    /**
     * Get the Events feature (injected via DI).
     *
     * @return Events|null The events feature or null.
     */
    public function events() : Events|null
    {
        return $this->events;
    }

    // ---------------------------------------------------------------------
    // 🔹 Lifecycle & Security Operations
    // ---------------------------------------------------------------------

    /**
     * Store a value in the session (automatically encrypted if configured).
     *
     * @param string   $key   The session key.
     * @param mixed    $value The value to store.
     * @param int|null $ttl   Time-to-live in seconds (null = never expires).
     */
    public function put(string $key, mixed $value, int|null $ttl = null, string|null $userId = null) : void
    {
        // Enforce security policies
        $this->policies?->enforce(data: $this->buildContext(userId: $userId));

        // Update registry activity
        $userId ??= $this->store->get(key: 'user_id');

        if ($userId) {
            $this->registry?->updateActivity(
                userId   : $userId,
                sessionId: $this->idProvider->current()
            );
        }

        $payload = $this->config->secure
            ? $this->encrypter->encrypt(value: $value)
            : $value;

        $this->store->put(key: $key, value: $payload, ttl: $ttl);

        $this->audit()?->record(
            event: 'session.data.put',
            data : compact('key', 'ttl')
        );

        $this->events()?->dispatch(
            event: 'session.data.put',
            data : compact(var_name: 'key')
        );
    }

    /**
     * Terminate the session securely.
     *
     * This is a comprehensive session termination that:
     * 1. Clears all session data
     * 2. Unregisters from multi-device registry
     * 3. Invalidates session cookies
     * 4. Records audit trail with termination reason
     * 5. Dispatches termination events
     *
     * Enterprise Features:
     * - Secure data wiping
     * - Multi-device session cleanup
     * - Comprehensive audit trail
     * - Reason tracking for compliance
     * - Event-driven notifications
     *
     * Common Reasons:
     * - 'logout' - User-initiated logout
     * - 'timeout' - Session timeout/inactivity
     * - 'security' - Security policy violation
     * - 'admin' - Administrative termination
     * - 'concurrent' - Concurrent login limit exceeded
     *
     * @param string|null $reason Termination reason for audit purposes.
     * @param string|null $userId
     *
     * @return void
     */
    public function terminate(string|null $reason = null, string|null $userId = null) : void
    {
        // Step 1: Get user ID before clearing (for audit)
        $reason ??= 'logout';
        $userId ??= $this->get(key: 'user_id');

        // Step 2: Unregister from multi-device registry
        $this->registry?->terminateSession(
            userId   : $userId,
            sessionId: $this->idProvider->current() // or session_id()
        );

        // Step 3: Audit the termination BEFORE clearing data
        $this->audit()?->record(
            event: 'session_terminated',
            data : [
                'user_id'          => $userId,
                'reason'           => $reason,
                'timestamp'        => time(),
                'session_duration' => time() - ($this->get(key: 'logged_in_at') ?? time())
            ]
        );

        // Step 4: Dispatch termination event
        $this->events()?->dispatch(
            event: 'session.terminated',
            data : compact('userId', 'reason')
        );

        // Step 5: Clear all session data
        $this->flush();

        // Step 6: Destroy PHP session (if using native sessions)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Flush (clear) all session data.
     */
    public function flush() : void
    {
        $this->store->flush();
        $this->audit()?->record(event: 'flush');
    }
}
