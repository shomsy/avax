<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Providers;

use Avax\HTTP\Session\Config\SessionConfig;
use Avax\HTTP\Session\Contracts\SessionContract;
use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Security\EncrypterFactory;
use Avax\HTTP\Session\Security\PolicyEnforcer;
use Avax\HTTP\Session\Security\CookieManager;
use Avax\HTTP\Session\Security\SessionRegistry;
use Avax\HTTP\Session\Security\SessionNonce;
use Avax\HTTP\Session\Adapters\SessionAdapter;
use Avax\HTTP\Session\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Contracts\FeatureInterface;
use Avax\HTTP\Session\Features\{Flash, Events, Audit, Snapshots};

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
final class SessionProvider implements SessionContract
{
    private ?Flash $flash = null;
    private ?Events $events = null;
    private ?Audit $audit = null;
    private ?Snapshots $snapshots = null;
    private EncrypterFactory $encrypter;
    private PolicyEnforcer $policyEnforcer;
    private CookieManager $cookieManager;
    private SessionAdapter $sessionAdapter;
    private ?SessionRegistry $registry = null;
    private ?SessionNonce $nonce = null;

    /**
     * @var array<FeatureInterface> Registered features
     */
    private array $features = [];

    /**
     * SessionProvider Constructor.
     *
     * @param Store               $store           The storage backend.
     * @param SessionConfig|null  $config          Optional configuration.
     * @param EncrypterFactory|null $encrypter     Optional encrypter factory.
     * @param PolicyEnforcer|null $policyEnforcer  Optional policy enforcer.
     * @param CookieManager|null  $cookieManager   Optional cookie manager.
     * @param SessionAdapter|null $sessionAdapter  Optional session adapter.
     */
    public function __construct(
        private Store $store,
        ?SessionConfig $config = null,
        ?EncrypterFactory $encrypter = null,
        ?PolicyEnforcer $policyEnforcer = null,
        ?CookieManager $cookieManager = null,
        ?SessionAdapter $sessionAdapter = null
    ) {
        $config ??= SessionConfig::default();

        // Initialize core services
        $this->encrypter = $encrypter ?? new EncrypterFactory();
        $this->policyEnforcer = $policyEnforcer ?? new PolicyEnforcer();
        $this->cookieManager = $cookieManager ?? CookieManager::lax();
        $this->sessionAdapter = $sessionAdapter ?? new SessionAdapter($this->cookieManager);
    }

    // ========================================
    // CORE OPERATIONS
    // ========================================

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
    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
        // Enforce policies before write (delegated to PolicyEnforcer)
        $this->policyEnforcer->enforce($this->all());

        // Auto-encrypt if key ends with '_secure' (using EncrypterFactory)
        if (str_ends_with($key, '_secure')) {
            $value = $this->encrypter->encrypt($value);
        }

        $this->store->put($key, $value);

        // Auto-TTL if specified
        if ($ttl !== null) {
            $this->store->put("_ttl.{$key}", time() + $ttl);
        }

        // Audit logging
        $this->audit?->record('stored', ['key' => $key, 'ttl' => $ttl]);

        // Dispatch event if events enabled
        $this->events?->dispatch('stored', ['key' => $key, 'ttl' => $ttl]);
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
    public function get(string $key, mixed $default = null): mixed
    {
        // Enforce policies before read (delegated to PolicyEnforcer)
        $this->policyEnforcer->enforce($this->all());

        // Check TTL expiration first
        if ($this->isExpired($key)) {
            $this->forget($key);
            return $default;
        }

        $value = $this->store->get($key, $default);

        // Auto-decrypt if encrypted (using EncrypterFactory with key rotation)
        if (str_ends_with($key, '_secure') && $value !== $default) {
            $value = $this->encrypter->decrypt($value);
        }

        // Audit logging
        $this->audit?->record('retrieved', ['key' => $key]);

        return $value;
    }

    /**
     * Check if a key exists in the session.
     *
     * @param string $key The session key.
     *
     * @return bool True if key exists and not expired.
     */
    public function has(string $key): bool
    {
        if ($this->isExpired($key)) {
            $this->forget($key);
            return false;
        }

        return $this->store->has($key);
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
    public function forget(string $key): void
    {
        $this->store->delete($key);
        $this->store->delete("_ttl.{$key}");

        // Audit logging
        $this->audit?->record('deleted', ['key' => $key]);

        // Dispatch event
        $this->events?->dispatch('deleted', ['key' => $key]);
    }

    /**
     * Get all session data.
     *
     * @return array<string, mixed> All session data.
     */
    public function all(): array
    {
        return $this->store->all();
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->store->flush();

        // Audit logging
        $this->audit?->record('flushed');

        // Dispatch event
        $this->events?->dispatch('flushed');
    }

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
    public function terminate(string $reason = 'logout'): void
    {
        // Audit logging
        $this->audit?->record('session_terminated', ['reason' => $reason]);

        // Terminate all features
        $this->terminateFeatures();

        // Clear all session data
        $this->flush();

        // Destroy server-side session (via SessionAdapter)
        $this->sessionAdapter->destroy();
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
    public function login(string $userId): void
    {
        // CRITICAL: Regenerate ID before setting user data (via SessionAdapter)
        $this->regenerateId();

        $this->put('user_id', $userId);
        $this->put('_authenticated', true);
        $this->put('_login_time', time());

        // Register session in SessionRegistry
        if ($this->registry !== null) {
            $this->registry->register($userId, $this->getId(), [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ]);
        }

        // Audit
        $this->audit?->record('session_login', ['user_id' => $userId]);
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
    public function elevatePrivileges(array $newRoles): void
    {
        // CRITICAL: Regenerate ID on privilege change
        $this->regenerateId();

        $this->put('roles', $newRoles);
        $this->put('_privilege_elevation_time', time());

        // Audit
        $this->audit?->record('privilege_elevation', ['roles' => $newRoles]);
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
    public function regenerateId(bool $deleteOldSession = true): void
    {
        $this->sessionAdapter->regenerateId($deleteOldSession);

        // Audit
        $this->audit?->record('session_regenerated');
    }

    /**
     * Get current session ID.
     *
     * Delegated to SessionAdapter.
     *
     * @return string Session ID.
     */
    public function getId(): string
    {
        return $this->sessionAdapter->getId();
    }

    // ========================================
    // DSL BUILDERS (Provider-Consumer Pattern)
    // ========================================

    /**
     * Create a contextual session consumer (Natural DSL).
     *
     * This is the natural, domain-oriented method for creating
     * session consumers. Reads like: "for this context...".
     *
     * @example
     *   $session->for('cart')->secure()->put('items', $items);
     *
     * @param string $context The consumer context.
     *
     * @return SessionConsumer Consumer for contextual operations.
     */
    public function for(string $context): SessionConsumer
    {
        return new SessionConsumer($context, $this);
    }

    /**
     * Create a scoped session consumer (Technical DSL).
     *
     * Alias for for() - provides technical namespace isolation.
     *
     * @example
     *   $session->scope('user')->ttl(3600)->put('id', 123);
     *
     * @param string $namespace The scope namespace.
     *
     * @return SessionConsumer Consumer for scoped operations.
     */
    public function scope(string $namespace): SessionConsumer
    {
        return $this->for($namespace);
    }

    /**
     * Access flash messages feature.
     *
     * Lazy-loaded on first access.
     *
     * @example
     *   $session->flash()->success('Saved!');
     *   $message = $session->flash()->get('success');
     *
     * @return Flash Flash messages manager.
     */
    public function flash(): Flash
    {
        return $this->flash ??= new Flash($this->store);
    }

    /**
     * Access events feature.
     *
     * Lazy-loaded on first access.
     *
     * @example
     *   $session->events()->listen('stored', fn($data) => logger()->info($data));
     *
     * @return Events Event dispatcher.
     */
    public function events(): Events
    {
        return $this->events ??= new Events();
    }

    // ========================================
    // POLICY SYSTEM
    // ========================================

    /**
     * Register a session policy.
     *
     * Policies are enforced on every put() and get() operation.
     * Delegated to PolicyEnforcer.
     *
     * @example
     *   $session->registerPolicy(new MaxIdlePolicy(900));
     *   $session->registerPolicy(new SecureOnlyPolicy());
     *
     * @param PolicyInterface $policy The policy to register.
     *
     * @return self Fluent interface.
     */
    public function registerPolicy(PolicyInterface $policy): self
    {
        $this->policyEnforcer->register($policy);
        return $this;
    }

    /**
     * Register multiple policies at once.
     *
     * @param array<PolicyInterface> $policies Policies to register.
     *
     * @return self Fluent interface.
     */
    public function registerPolicies(array $policies): self
    {
        $this->policyEnforcer->registerMany($policies);
        return $this;
    }

    // ========================================
    // AUDIT SYSTEM
    // ========================================

    /**
     * Get audit instance (if enabled).
     *
     * @return Audit|null Audit instance or null.
     */
    public function audit(): ?Audit
    {
        return $this->audit;
    }

    /**
     * Enable audit logging.
     *
     * @example
     *   $session->enableAudit('/var/log/session.log');
     *
     * @param string|null $path Optional log file path.
     *
     * @return self Fluent interface.
     */
    public function enableAudit(?string $path = null): self
    {
        $this->audit = new Audit($path);
        return $this;
    }

    // ========================================
    // SNAPSHOT SYSTEM
    // ========================================

    /**
     * Access snapshots feature.
     *
     * Lazy-loaded on first access.
     *
     * @return Snapshots Snapshot manager.
     */
    public function snapshots(): Snapshots
    {
        return $this->snapshots ??= new Snapshots();
    }

    /**
     * Create a snapshot of current session state.
     *
     * @example
     *   $session->snapshot('before_checkout');
     *
     * @param string $name Snapshot name.
     *
     * @return void
     */
    public function snapshot(string $name): void
    {
        $this->snapshots()->snapshot($name, $this->all());

        // Audit logging
        $this->audit?->record('snapshot', ['name' => $name]);
    }

    /**
     * Restore session state from a snapshot.
     *
     * @example
     *   $session->restore('before_checkout');
     *
     * @param string $name Snapshot name.
     *
     * @return void
     */
    public function restore(string $name): void
    {
        $data = $this->snapshots()->restore($name);

        if ($data === null) {
            return;
        }

        $this->flush();

        foreach ($data as $key => $value) {
            $this->put($key, $value);
        }

        // Audit logging
        $this->audit?->record('restored', ['name' => $name]);
    }

    // ========================================
    // SMART HELPERS
    // ========================================

    /**
     * Remember pattern - lazy evaluation with caching.
     *
     * Retrieves value if exists, otherwise executes callback and stores result.
     *
     * @example
     *   $user = $session->remember('current_user', fn() => User::find($id));
     *
     * @param string   $key      The cache key.
     * @param callable $callback Callback to generate value.
     * @param int|null $ttl      Optional TTL in seconds.
     *
     * @return mixed The cached or generated value.
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Create a temporary session consumer.
     *
     * Convenience method for consumers with TTL.
     *
     * @example
     *   $session->temporary(300)->put('otp', '123456');
     *
     * @param int $seconds TTL in seconds.
     *
     * @return SessionConsumer Consumer with TTL.
     */
    public function temporary(int $seconds): SessionConsumer
    {
        return $this->for('temp')->ttl($seconds);
    }

    // ========================================
    // INTERNAL HELPERS
    // ========================================

    /**
     * Check if a key has expired.
     *
     * @param string $key The session key.
     *
     * @return bool True if expired.
     */
    private function isExpired(string $key): bool
    {
        $expiry = $this->store->get("_ttl.{$key}");
        return $expiry !== null && time() > $expiry;
    }

    // ========================================
    // SERVICE ACCESSORS
    // ========================================

    /**
     * Get EncrypterFactory instance.
     *
     * @return EncrypterFactory Encrypter factory.
     */
    public function getEncrypter(): EncrypterFactory
    {
        return $this->encrypter;
    }

    /**
     * Get PolicyEnforcer instance.
     *
     * @return PolicyEnforcer Policy enforcer.
     */
    public function getPolicyEnforcer(): PolicyEnforcer
    {
        return $this->policyEnforcer;
    }

    /**
     * Get CookieManager instance.
     *
     * @return CookieManager Cookie manager.
     */
    public function getCookieManager(): CookieManager
    {
        return $this->cookieManager;
    }

    /**
     * Get SessionAdapter instance.
     *
     * @return SessionAdapter Session adapter.
     */
    public function getSessionAdapter(): SessionAdapter
    {
        return $this->sessionAdapter;
    }

    /**
     * Get SessionRegistry instance.
     *
     * @return SessionRegistry|null Session registry or null.
     */
    public function getRegistry(): ?SessionRegistry
    {
        return $this->registry;
    }

    /**
     * Enable SessionRegistry for multi-device control.
     *
     * @return self Fluent interface.
     */
    public function enableRegistry(): self
    {
        $this->registry = new SessionRegistry($this->store);
        return $this;
    }

    /**
     * Get SessionNonce instance.
     *
     * @return SessionNonce|null Session nonce or null.
     */
    public function getNonce(): ?SessionNonce
    {
        return $this->nonce;
    }

    /**
     * Enable SessionNonce for replay attack prevention.
     *
     * @return self Fluent interface.
     */
    public function enableNonce(): self
    {
        $this->nonce = new SessionNonce($this->store);
        return $this;
    }

    // ========================================
    // FEATURE LIFECYCLE MANAGEMENT
    // ========================================

    /**
     * Boot all registered features.
     *
     * @return void
     */
    private function bootFeatures(): void
    {
        foreach ($this->features as $feature) {
            if ($feature instanceof FeatureInterface) {
                $feature->boot();
            }
        }
    }

    /**
     * Terminate all registered features.
     *
     * @return void
     */
    private function terminateFeatures(): void
    {
        foreach ($this->features as $feature) {
            if ($feature instanceof FeatureInterface) {
                $feature->terminate();
            }
        }
    }

    /**
     * Register a feature.
     *
     * @param FeatureInterface $feature Feature to register.
     *
     * @return self Fluent interface.
     */
    public function registerFeature(FeatureInterface $feature): self
    {
        $this->features[$feature->getName()] = $feature;
        $feature->boot();
        return $this;
    }
}
