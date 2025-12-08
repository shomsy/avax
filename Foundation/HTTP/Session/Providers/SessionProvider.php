<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Providers;

use Avax\HTTP\Session\Adapters\SessionAdapter;
use Avax\HTTP\Session\Config\SessionConfig;
use Avax\HTTP\Session\Contracts\FeatureInterface;
use Avax\HTTP\Session\Contracts\SessionContract;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Features\{Audit, Events, Flash, Snapshots};
use Avax\HTTP\Session\Security\CookieManager;
use Avax\HTTP\Session\Security\EncrypterFactory;
use Avax\HTTP\Session\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Security\PolicyEnforcer;
use Avax\HTTP\Session\Security\SessionNonce;
use Avax\HTTP\Session\Security\SessionRegistry;
use Override;
use SensitiveParameter;

/**
 * SessionProvider - Enterprise Session Provider V4.0
 *
 * Acts as the core session provider — responsible for managing
 * session lifecycle, persistence, and behavioral features.
 *
 * Provider-Consumer Pattern:
 * - SessionProvider = Provider (aggregate root, lifecycle management)
 * - SessionConsumer = Consumer (contextual DSL adapter)
 *
 * V4.0 Enterprise Edition Features:
 * - EncrypterFactory: Real AES-256-GCM encryption with key rotation
 * - PolicyEnforcer: Centralized policy enforcement
 * - CookieManager: OWASP-compliant cookie security
 * - SessionAdapter: Testable session operations
 * - SessionRegistry: Multi-device session control
 * - SessionNonce: Replay attack prevention
 * - FeatureInterface: Unified feature lifecycle
 *
 * Built-in Features (Zero Configuration):
 * - Auto-encryption (via _secure suffix convention)
 * - Auto-TTL (via ttl parameter)
 * - Namespacing (via scope() / for() builders)
 * - Remember pattern (lazy evaluation)
 * - Policy enforcement (security rules)
 * - Audit logging (observability)
 * - Snapshots (state management)
 *
 * Enterprise Rules:
 * - Smart Conventions: _secure suffix triggers auto-encryption
 * - Lazy Loading: Flash, Events, Audit, Snapshots created only when needed
 * - Dependency Injection: All services injected for testability
 * - Zero Ceremony: Minimal interfaces
 *
 * @example Basic usage
 *   $session = new SessionProvider($store);
 *   $session->put('user_id', 123);
 *   $userId = $session->get('user_id');
 *
 * @example Natural DSL with for()
 *   $session->for('cart')
 *       ->secure()
 *       ->ttl(3600)
 *       ->put('items', $items);
 *
 * @example With Policies
 *   $session->registerPolicy(new MaxIdlePolicy(900));
 *   $session->registerPolicy(new SecureOnlyPolicy());
 *
 * @example With Audit
 *   $session->enableAudit('/var/log/session.log');
 *
 * @example With Snapshots
 *   $session->snapshot('before_checkout');
 *   // ... later
 *   $session->restore('before_checkout');
 *
 * @package Avax\HTTP\Session
 */
final class SessionProvider implements SessionContract, SessionInterface
{
    private Flash|null           $flash     = null;
    private Events|null          $events    = null;
    private Audit|null           $audit     = null;
    private Snapshots|null       $snapshots = null;
    private EncrypterFactory     $encrypter;
    private PolicyEnforcer       $policyEnforcer;
    private CookieManager        $cookieManager;
    private SessionAdapter       $sessionAdapter;
    private SessionRegistry|null $registry  = null;
    private SessionNonce|null    $nonce     = null;

    /**
     * @var array<FeatureInterface> Registered features
     */
    private array $features = [];

    /**
     * SessionProvider Constructor.
     *
     * @param Store                 $store          The storage backend.
     * @param SessionConfig|null    $config         Optional configuration.
     * @param EncrypterFactory|null $encrypter      Optional encrypter factory.
     * @param PolicyEnforcer|null   $policyEnforcer Optional policy enforcer.
     * @param CookieManager|null    $cookieManager  Optional cookie manager.
     * @param SessionAdapter|null   $sessionAdapter Optional session adapter.
     */
    public function __construct(
        private Store                             $store,
        SessionConfig|null                        $config = null,
        EncrypterFactory|null                     $encrypter = null,
        PolicyEnforcer|null                       $policyEnforcer = null,
        CookieManager|null                        $cookieManager = null,
        #[SensitiveParameter] SessionAdapter|null $sessionAdapter = null
    )
    {
        $config ??= SessionConfig::default();

        // Initialize core services
        $this->encrypter      = $encrypter ?? new EncrypterFactory();
        $this->policyEnforcer = $policyEnforcer ?? new PolicyEnforcer();
        $this->cookieManager  = $cookieManager ?? CookieManager::lax();
        $this->sessionAdapter = $sessionAdapter ?? new SessionAdapter(cookieManager: $this->cookieManager);
    }

    // ========================================
    // CORE OPERATIONS
    // ========================================

    /**
     * Terminate session securely.
     *
     * OWASP ASVS 3.2.3 Compliant
     *
     * Performs complete session cleanup:
     * - Terminates all features (via FeatureInterface)
     * - Clears all data
     * - Destroys server session (via SessionAdapter)
     * - Removes client cookie (via CookieManager)
     * - Prevents session reuse
     * - Audit logs termination
     *
     * @param string $reason Termination reason (for audit).
     *
     * @return void
     */
    #[Override]
    public function terminate(string $reason = 'logout') : void
    {
        // Audit logging
        $this->audit?->record(event: 'session_terminated', data: compact(var_name: 'reason'));

        // Terminate all features
        $this->terminateFeatures();

        // Clear all session data
        $this->flush();

        // Destroy server-side session (via SessionAdapter)
        $this->sessionAdapter->destroy();
    }

    /**
     * Terminate all registered features.
     *
     * @return void
     */
    private function terminateFeatures() : void
    {
        foreach ($this->features as $feature) {
            if ($feature instanceof FeatureInterface) {
                $feature->terminate();
            }
        }
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    #[Override]
    public function flush() : void
    {
        $this->store->flush();

        // Audit logging
        $this->audit?->record(event: 'flushed');

        // Dispatch event
        $this->events?->dispatch(event: 'flushed');
    }

    /**
     * Login with automatic session regeneration.
     *
     * OWASP ASVS 3.2.1 Compliant
     *
     * Automatically regenerates session ID to prevent fixation.
     * Registers session in SessionRegistry for multi-device control.
     *
     * @param string $userId User identifier.
     *
     * @return void
     */
    #[Override]
    public function login(string $userId) : void
    {
        // CRITICAL: Regenerate ID before setting user data (via SessionAdapter)
        $this->regenerateId();

        $this->put(key: 'user_id', value: $userId);
        $this->put(key: '_authenticated', value: true);
        $this->put(key: '_login_time', value: time());

        // Register session in SessionRegistry
        if ($this->registry !== null) {
            $this->registry->register(
                userId   : $userId,
                sessionId: $this->getId(),
                metadata : [
                    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ]
            );
        }

        // Audit
        $this->audit?->record(event: 'session_login', data: ['user_id' => $userId]);
    }

    /**
     * Regenerate session ID.
     *
     * OWASP ASVS 3.2.1 Compliant
     *
     * Prevents session fixation attacks.
     * Delegated to SessionAdapter for testability.
     *
     * @param bool $deleteOldSession Delete old session data.
     *
     * @return void
     */
    #[Override]
    public function regenerateId(bool $deleteOldSession = true) : void
    {
        $this->sessionAdapter->regenerateId(deleteOldSession: $deleteOldSession);

        // Audit
        $this->audit?->record(event: 'session_regenerated');
    }

    /**
     * Store a value in the session.
     *
     * Smart Conventions:
     * - Keys ending with '_secure' are auto-encrypted (via EncrypterFactory)
     * - TTL parameter sets automatic expiration
     * - Policy enforcement (via PolicyEnforcer)
     * - Audit logging (if enabled)
     *
     * @param string   $key   The session key.
     * @param mixed    $value The value to store.
     * @param int|null $ttl   Optional time-to-live in seconds.
     *
     * @return void
     */
    #[Override]
    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        // Enforce policies before write (delegated to PolicyEnforcer)
        $this->policyEnforcer->enforce(data: $this->all());

        // Auto-encrypt if key ends with '_secure' (using EncrypterFactory)
        if (str_ends_with(haystack: $key, needle: '_secure')) {
            $value = $this->encrypter->encrypt(value: $value);
        }

        $this->store->put(key: $key, value: $value);

        // Auto-TTL if specified
        if ($ttl !== null) {
            $this->store->put(
                key  : "_ttl.{$key}",
                value: time() + $ttl
            );
        }

        // Audit logging
        $this->audit?->record(event: 'stored', data: compact('key', 'ttl'));

        // Dispatch event if events enabled
        $this->events?->dispatch(event: 'stored', data: compact('key', 'ttl'));
    }

    /**
     * Get all session data.
     *
     * @return array<string, mixed> All session data.
     */
    #[Override]
    public function all() : array
    {
        return $this->store->all();
    }

    /**
     * Get current session ID.
     *
     * Delegated to SessionAdapter.
     *
     * @return string Session ID.
     */
    #[Override]
    public function getId() : string
    {
        return $this->sessionAdapter->getId();
    }

    /**
     * Elevate user privileges.
     *
     * OWASP ASVS 3.2.1 Compliant
     *
     * Regenerates session ID when user gains elevated permissions.
     *
     * @param array<string> $newRoles New user roles.
     *
     * @return void
     */
    public function elevatePrivileges(array $newRoles) : void
    {
        // CRITICAL: Regenerate ID on privilege change
        $this->regenerateId();

        $this->put(key: 'roles', value: $newRoles);
        $this->put(key: '_privilege_elevation_time', value: time());

        // Audit
        $this->audit?->record(event: 'privilege_elevation', data: ['roles' => $newRoles]);
    }

    /**
     * Alias for put() to ease transition from the old API.
     */
    #[Override]
    public function set(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->put(key: $key, value: $value, ttl: $ttl);
    }

    /**
     * Alias for forget() to ease transition from the old API.
     */
    #[Override]
    public function remove(string $key) : void
    {
        $this->forget(key: $key);
    }

    /**
     * Remove a value from the session.
     *
     * Also removes associated TTL metadata.
     *
     * @param string $key The session key.
     *
     * @return void
     */
    #[Override]
    public function forget(string $key) : void
    {
        $this->store->delete(key: $key);
        $this->store->delete(key: "_ttl.{$key}");

        // Audit logging
        $this->audit?->record(event: 'deleted', data: compact(var_name: 'key'));

        // Dispatch event
        $this->events?->dispatch(event: 'deleted', data: compact(var_name: 'key'));
    }

    /**
     * Alias for forget() to ease transition from the old API.
     */
    #[Override]
    public function delete(string $key) : void
    {
        $this->forget(key: $key);
    }

    /**
     * Start session (idempotent) through the adapter.
     */
    #[Override]
    public function start() : bool
    {
        return $this->sessionAdapter->start();
    }

    /**
     * Proxy flash getter for convenience.
     */
    public function getFlash(string $key, mixed $default = null) : string|null
    {
        return $this->flash()->get(key: $key, default: $default);
    }

    /**
     * Retrieve a value from the session.
     *
     * Smart Conventions:
     * - Auto-checks TTL expiration
     * - Auto-decrypts '_secure' suffixed keys (via EncrypterFactory)
     * - Policy enforcement (via PolicyEnforcer)
     *
     * @param string $key     The session key.
     * @param mixed  $default Default value if key doesn't exist.
     *
     * @return mixed The retrieved value or default.
     */
    #[Override]
    public function get(string $key, mixed $default = null) : mixed
    {
        // Enforce policies before read (delegated to PolicyEnforcer)
        $this->policyEnforcer->enforce(data: $this->all());

        // Check TTL expiration first
        if ($this->isExpired(key: $key)) {
            $this->forget(key: $key);

            return $default;
        }

        $value = $this->store->get(key: $key, default: $default);

        // Auto-decrypt if encrypted (using EncrypterFactory with key rotation)
        if (str_ends_with(haystack: $key, needle: '_secure') && $value !== $default) {
            $value = $this->encrypter->decrypt(payload: $value);
        }

        // Audit logging
        $this->audit?->record(event: 'retrieved', data: compact(var_name: 'key'));

        return $value;
    }

    // ========================================
    // DSL BUILDERS (Provider-Consumer Pattern)
    // ========================================

    /**
     * Check if a key has expired.
     *
     * @param string $key The session key.
     *
     * @return bool True if expired.
     */
    private function isExpired(string $key) : bool
    {
        $expiry = $this->store->get(key: "_ttl.{$key}");

        return $expiry !== null && time() > $expiry;
    }

    /**
     * Access flash messages feature.
     *
     * Lazy-loaded on first access.
     *
     * @return Flash Flash messages manager.
     * @example
     *   $session->flash()->success('Saved!');
     *   $message = $session->flash()->get('success');
     *
     */
    #[Override]
    public function flash() : Flash
    {
        return $this->flash ??= new Flash(store: $this->store);
    }

    /**
     * Create a scoped session consumer (Technical DSL).
     *
     * Alias for for() - provides technical namespace isolation.
     *
     * @param string $namespace The scope namespace.
     *
     * @return SessionConsumer Consumer for scoped operations.
     * @example
     *   $session->scope('user')->ttl(3600)->put('id', 123);
     *
     */
    public function scope(string $namespace) : SessionConsumer
    {
        return $this->for(context: $namespace);
    }

    /**
     * Create a contextual session consumer (Natural DSL).
     *
     * This is the natural, domain-oriented method for creating
     * session consumers. Reads like: "for this context...".
     *
     * @param string $context The consumer context.
     *
     * @return SessionConsumer Consumer for contextual operations.
     * @example
     *   $session->for('cart')->secure()->put('items', $items);
     *
     */
    public function for(string $context) : SessionConsumer
    {
        return new SessionConsumer(namespace: $context, provider: $this);
    }

    // ========================================
    // POLICY SYSTEM
    // ========================================

    /**
     * Access events feature.
     *
     * Lazy-loaded on first access.
     *
     * @return Events Event dispatcher.
     * @example
     *   $session->events()->listen('stored', fn($data) => logger()->info($data));
     *
     */
    #[Override]
    public function events() : Events
    {
        return $this->events ??= new Events();
    }

    /**
     * Register a session policy.
     *
     * Policies are enforced on every put() and get() operation.
     * Delegated to PolicyEnforcer.
     *
     * @param PolicyInterface $policy The policy to register.
     *
     * @return self Fluent interface.
     * @example
     *   $session->registerPolicy(new MaxIdlePolicy(900));
     *   $session->registerPolicy(new SecureOnlyPolicy());
     *
     */
    public function registerPolicy(PolicyInterface $policy) : self
    {
        $this->policyEnforcer->register(policy: $policy);

        return $this;
    }

    // ========================================
    // AUDIT SYSTEM
    // ========================================

    /**
     * Register multiple policies at once.
     *
     * @param array<PolicyInterface> $policies Policies to register.
     *
     * @return self Fluent interface.
     */
    public function registerPolicies(array $policies) : self
    {
        $this->policyEnforcer->registerMany(policies: $policies);

        return $this;
    }

    /**
     * Get audit instance (if enabled).
     *
     * @return Audit|null Audit instance or null.
     */
    public function audit() : Audit|null
    {
        return $this->audit;
    }

    // ========================================
    // SNAPSHOT SYSTEM
    // ========================================

    /**
     * Enable audit logging.
     *
     * @param string|null $path Optional log file path.
     *
     * @return self Fluent interface.
     * @example
     *   $session->enableAudit('/var/log/session.log');
     *
     */
    public function enableAudit(string|null $path = null) : self
    {
        $this->audit = new Audit(logPath: $path);

        return $this;
    }

    /**
     * Create a snapshot of current session state.
     *
     * @param string $name Snapshot name.
     *
     * @return void
     * @example
     *   $session->snapshot('before_checkout');
     *
     */
    public function snapshot(string $name) : void
    {
        $this->snapshots()->snapshot(
            name: $name,
            data: $this->all()
        );

        // Audit logging
        $this->audit?->record(event: 'snapshot', data: compact(var_name: 'name'));
    }

    /**
     * Access snapshots feature.
     *
     * Lazy-loaded on first access.
     *
     * @return Snapshots Snapshot manager.
     */
    public function snapshots() : Snapshots
    {
        return $this->snapshots ??= new Snapshots();
    }

    // ========================================
    // SMART HELPERS
    // ========================================

    /**
     * Restore session state from a snapshot.
     *
     * @param string $name Snapshot name.
     *
     * @return void
     * @example
     *   $session->restore('before_checkout');
     *
     */
    public function restore(string $name) : void
    {
        $data = $this->snapshots()->restore(name: $name);

        if ($data === null) {
            return;
        }

        $this->flush();

        foreach ($data as $key => $value) {
            $this->put(key: $key, value: $value);
        }

        // Audit logging
        $this->audit?->record(event: 'restored', data: compact(var_name: 'name'));
    }

    /**
     * Remember pattern - lazy evaluation with caching.
     *
     * Retrieves value if exists, otherwise executes callback and stores result.
     *
     * @param string   $key      The cache key.
     * @param callable $callback Callback to generate value.
     * @param int|null $ttl      Optional TTL in seconds.
     *
     * @return mixed The cached or generated value.
     * @example
     *   $user = $session->remember('current_user', fn() => User::find($id));
     *
     */
    #[Override]
    public function remember(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        if ($this->has(key: $key)) {
            return $this->get(key: $key);
        }

        $value = $callback();
        $this->put(key: $key, value: $value, ttl: $ttl);

        return $value;
    }

    // ========================================
    // INTERNAL HELPERS
    // ========================================

    /**
     * Check if a key exists in the session.
     *
     * @param string $key The session key.
     *
     * @return bool True if key exists and not expired.
     */
    #[Override]
    public function has(string $key) : bool
    {
        if ($this->isExpired(key: $key)) {
            $this->forget(key: $key);

            return false;
        }

        return $this->store->has(key: $key);
    }

    // ========================================
    // SERVICE ACCESSORS
    // ========================================

    /**
     * Create a temporary session consumer.
     *
     * Convenience method for consumers with TTL.
     *
     * @param int $seconds TTL in seconds.
     *
     * @return SessionConsumer Consumer with TTL.
     * @example
     *   $session->temporary(300)->put('otp', '123456');
     *
     */
    public function temporary(int $seconds) : SessionConsumer
    {
        return $this->for(context: 'temp')->ttl(seconds: $seconds);
    }

    /**
     * Get EncrypterFactory instance.
     *
     * @return EncrypterFactory Encrypter factory.
     */
    public function getEncrypter() : EncrypterFactory
    {
        return $this->encrypter;
    }

    /**
     * Get PolicyEnforcer instance.
     *
     * @return PolicyEnforcer Policy enforcer.
     */
    public function getPolicyEnforcer() : PolicyEnforcer
    {
        return $this->policyEnforcer;
    }

    /**
     * Get CookieManager instance.
     *
     * @return CookieManager Cookie manager.
     */
    public function getCookieManager() : CookieManager
    {
        return $this->cookieManager;
    }

    /**
     * Get SessionAdapter instance.
     *
     * @return SessionAdapter Session adapter.
     */
    public function getSessionAdapter() : SessionAdapter
    {
        return $this->sessionAdapter;
    }

    /**
     * Get SessionRegistry instance.
     *
     * @return SessionRegistry|null Session registry or null.
     */
    public function getRegistry() : SessionRegistry|null
    {
        return $this->registry;
    }

    /**
     * Enable SessionRegistry for multi-device control.
     *
     * @return self Fluent interface.
     */
    public function enableRegistry() : self
    {
        $this->registry = new SessionRegistry(store: $this->store);

        return $this;
    }

    /**
     * Get SessionNonce instance.
     *
     * @return SessionNonce|null Session nonce or null.
     */
    public function getNonce() : SessionNonce|null
    {
        return $this->nonce;
    }

    // ========================================
    // FEATURE LIFECYCLE MANAGEMENT
    // ========================================

    /**
     * Enable SessionNonce for replay attack prevention.
     *
     * @return self Fluent interface.
     */
    public function enableNonce() : self
    {
        $this->nonce = new SessionNonce(store: $this->store);

        return $this;
    }

    /**
     * Register a feature.
     *
     * @param FeatureInterface $feature Feature to register.
     *
     * @return self Fluent interface.
     */
    public function registerFeature(FeatureInterface $feature) : self
    {
        $this->features[$feature->getName()] = $feature;
        $feature->boot();

        return $this;
    }

    /**
     * Boot all registered features.
     *
     * @return void
     */
    private function bootFeatures() : void
    {
        foreach ($this->features as $feature) {
            if ($feature instanceof FeatureInterface) {
                $feature->boot();
            }
        }
    }
}
