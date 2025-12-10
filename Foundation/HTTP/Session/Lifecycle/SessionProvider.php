<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Lifecycle;

use Avax\HTTP\Session\Config\SessionConfig;
use Avax\HTTP\Session\Contracts\FeatureInterface;
use Avax\HTTP\Session\Contracts\Security\Encrypter;
use Avax\HTTP\Session\Data\Recovery;
use Avax\HTTP\Session\Data\StoreInterface;
use Avax\HTTP\Session\Features\Events;
use Avax\HTTP\Session\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Security\SessionRegistry;
use Avax\HTTP\Session\Security\SessionSignature;
use Throwable;

/**
 * Class SessionProvider
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
 * @package Avax\HTTP\Session\Lifecycle
 */
final class SessionProvider
{
    /**
     * @var array<string, FeatureInterface>
     * Registered non-core features (e.g. Audit, Metrics, Events).
     */
    private array $features = [];

    /**
     * SessionProvider Constructor.
     *
     * @param StoreInterface        $store     Persistent session store driver.
     * @param SessionConfig         $config    Immutable configuration object.
     * @param Encrypter             $encrypter Encryption engine for secure payloads.
     * @param Recovery              $recovery  Recovery manager for backup and rollback.
     * @param SessionSignature|null $signature Optional integrity validator for session IDs.
     * @param PolicyInterface|null  $policies  Optional policy group or composite.
     * @param SessionRegistry|null  $registry  Optional multi-login registry handler.
     */
    public function __construct(
        private readonly StoreInterface        $store,
        private readonly SessionConfig         $config,
        private readonly Encrypter             $encrypter,
        private readonly Recovery              $recovery,
        private readonly SessionSignature|null $signature = null,
        private readonly PolicyInterface|null  $policies = null,
        private readonly SessionRegistry|null  $registry = null
    ) {}

    // ---------------------------------------------------------------------
    // 🔹 Core Accessors
    // ---------------------------------------------------------------------

    /**
     * Get the session data store.
     */
    public function store() : StoreInterface
    {
        return $this->store;
    }

    /**
     * Get the immutable configuration for this session.
     */
    public function config() : SessionConfig
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
    // 🔹 Feature Registration System
    // ---------------------------------------------------------------------

    /**
     * Register a feature (e.g. Audit, Events, Metrics).
     *
     * @param FeatureInterface $feature The feature instance.
     */
    public function registerFeature(FeatureInterface $feature) : void
    {
        $feature->boot();
        $this->features[$feature->getName()] = $feature;
    }

    /**
     * Store a value in the session (automatically encrypted if configured).
     *
     * @param string $key   The session key.
     * @param mixed  $value The value to store.
     */
    public function put(string $key, mixed $value) : void
    {
        $payload = $this->config->secure
            ? $this->encrypter->encrypt(value: $value)
            : $value;

        $this->store->put(key: $key, value: $payload);

        $this->audit()?->record(event: 'put', data: compact(var_name: 'key'));
    }

    /**
     * Shortcut accessor for the registered Audit feature.
     */
    public function audit() : FeatureInterface|null
    {
        return $this->feature(name: 'audit');
    }

    /**
     * Retrieve a registered feature by name.
     *
     * @param string $name The feature name.
     *
     * @return FeatureInterface|null The feature instance, or null if not registered.
     */
    public function feature(string $name) : FeatureInterface|null
    {
        return $this->features[$name] ?? null;
    }


    // ---------------------------------------------------------------------
    // 🔹 Session Data Operations
    // ---------------------------------------------------------------------

    /**
     * Shortcut accessor for the registered Events feature.
     *
     * Provides access to the event bus for real-time session notifications.
     *
     * @return \Avax\HTTP\Session\Contracts\FeatureInterface|null
     */
    public function events() : Events|null
    {
        return $this->feature(name: 'events');
    }

    /**
     * Retrieve a value from the session store (auto-decrypted if necessary).
     *
     * @param string $key     Session key.
     * @param mixed  $default Default value if key does not exist.
     *
     * @return mixed The session value or the default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $data = $this->store->get(key: $key, default: $default);

        if ($data === null) {
            return $default;
        }

        if ($this->config->secure && is_string(value: $data)) {
            $data = $this->encrypter->decrypt($data);
        }

        $this->audit()?->record(event: 'get', data: compact(var_name: 'key'));

        return $data;
    }

    /**
     * Flush (clear) all session data.
     */
    public function flush() : void
    {
        $this->store->flush();
        $this->audit()?->record(event: 'flush');
    }

    // ---------------------------------------------------------------------
    // 🔹 Recovery Convenience Wrappers
    // ---------------------------------------------------------------------

    /**
     * Create a recovery snapshot (savepoint of current session state).
     */
    public function snapshot() : void
    {
        $this->recovery->snapshot();
        $this->audit()?->record(event: 'snapshot_created', data: ['time' => time()]);
    }

    /**
     * Restore the session to the last valid snapshot.
     */
    public function restore() : void
    {
        $this->recovery->restore();
        $this->audit()?->record(event: 'session_restored', data: ['time' => time()]);
    }

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
}
