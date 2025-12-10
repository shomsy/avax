<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Data\Recovery;
use Avax\HTTP\Session\Features\{Audit, Events};
use Avax\HTTP\Session\Lifecycle\{SessionConsumer, SessionNonce, SessionProvider, SessionRegistry};
use Avax\HTTP\Session\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Security\SessionNonce;
use Avax\HTTP\Session\Security\SessionRegistry;
use Throwable;

/**
 * 🧠 Session - Unified Enterprise Facade
 * ------------------------------------------------------------
 * The all-in-one orchestrator for Avax's session engine.
 *
 * This class unifies multiple session subsystems into a single,
 * developer-friendly API — covering:
 *
 * - Secure data storage (encrypted, signed)
 * - TTL & namespace isolation
 * - Flash messages
 * - Event bus integration
 * - Auditing (PSR-3 compliant)
 * - Recovery snapshots & rollback
 * - Multi-device registry tracking
 * - Security policies
 * - Nonce-based replay protection
 *
 * 💡 Enterprise features are wired automatically via DI.
 *
 * @version 5.0
 * @package Avax\HTTP\Session
 * @author  Milos
 */
final readonly class Session
{
    public function __construct(
        private SessionProvider $provider
    ) {}

    // ------------------------------------------------------------
    // 🧱 Core Data API
    // ------------------------------------------------------------

    public function put(string $key, mixed $value, ?int $ttl = null) : void
    {
        $this->provider->put($key, $value, $ttl);
        $this->audit()->record('put', ['key' => $key]);
        $this->events()->dispatch('session.stored', ['key' => $key]);
    }

    public function audit() : Audit
    {
        return $this->provider->audit();
    }

    public function events() : Events
    {
        return $this->provider->events();
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $value = $this->provider->get($key, $default);
        $this->events()->dispatch('session.retrieved', ['key' => $key]);

        return $value;
    }

    public function has(string $key) : bool
    {
        return $this->provider->has($key);
    }

    public function forget(string $key) : void
    {
        $this->provider->forget($key);
        $this->events()->dispatch('session.deleted', ['key' => $key]);
        $this->audit()->record('forget', ['key' => $key]);
    }

    // ------------------------------------------------------------
    // 🧠 Contextual Consumers
    // ------------------------------------------------------------

    public function flush() : void
    {
        $this->provider->flush();
        $this->audit()->record('flush', []);
        $this->events()->dispatch('session.flushed');
    }

    public function for(string $context) : SessionConsumer
    {
        return $this->provider->for($context);
    }

    // ------------------------------------------------------------
    // 🪶 Observability (Audit, Events)
    // ------------------------------------------------------------

    public function scope(string $namespace) : SessionConsumer
    {
        return $this->provider->scope($namespace);
    }

    public function registerPolicy(PolicyInterface $policy) : self
    {
        $this->provider->registerPolicy($policy);
        $this->audit()->record('policy.registered', ['policy' => get_class($policy)]);

        return $this;
    }

    // ------------------------------------------------------------
    // ⚖️ Policies & Security
    // ------------------------------------------------------------

    public function enableAudit(?string $path = null) : self
    {
        $this->provider->enableAudit($path);

        return $this;
    }

    public function snapshot(string $name) : void
    {
        $this->recovery()->snapshot($name);
        $this->audit()->record('snapshot.created', ['name' => $name]);
    }

    // ------------------------------------------------------------
    // 💾 Recovery API
    // ------------------------------------------------------------

    public function recovery() : Recovery
    {
        return $this->provider->recovery();
    }

    public function restore(string $name) : void
    {
        $this->recovery()->restore($name);
        $this->audit()->record('snapshot.restored', ['name' => $name]);
    }

    public function transaction(callable $callback) : void
    {
        $recovery = $this->recovery();
        $recovery->beginTransaction();

        try {
            $callback($this);
            $recovery->commit();
            $this->events()->dispatch('session.transaction.commit');
        } catch (Throwable $e) {
            $recovery->rollback();
            $this->audit()->record('transaction.rollback', ['error' => $e->getMessage()]);
            $this->events()->dispatch('session.transaction.rollback');
            throw $e;
        }
    }

    public function regenerate() : void
    {
        $this->provider->regenerate();
        $this->audit()->record('session.regenerated');
    }

    // ------------------------------------------------------------
    // ⚙️ Session Lifecycle
    // ------------------------------------------------------------

    public function login(string $userId, array $data = []) : void
    {
        $this->provider->login($userId, $data);
        $this->audit()->record('login', ['user' => $userId]);
        $this->events()->dispatch('session.login', ['user' => $userId]);
    }

    public function terminate(string $reason = 'logout') : void
    {
        $this->provider->terminate($reason);
        $this->audit()->record('terminate', ['reason' => $reason]);
        $this->events()->dispatch('session.terminated', ['reason' => $reason]);
    }

    public function getRegistry() : SessionRegistry
    {
        return $this->provider->getRegistry();
    }

    // ------------------------------------------------------------
    // 🔐 Security Subsystems
    // ------------------------------------------------------------

    public function getNonce() : SessionNonce
    {
        return $this->provider->getNonce();
    }

    public function remember(string $key, callable $callback, ?int $ttl = null) : mixed
    {
        return $this->provider->remember($key, $callback, $ttl);
    }

    // ------------------------------------------------------------
    // 🪄 Lazy Evaluation (Cache-like)
    // ------------------------------------------------------------

    public function stats() : array
    {
        return [
            'total_keys' => count($this->provider->all()),
            'features'   => $this->provider->registeredFeatures(),
            'policies'   => $this->provider->activePolicies(),
        ];
    }

    // ------------------------------------------------------------
    // 🩺 Diagnostics
    // ------------------------------------------------------------

    public function all() : array
    {
        return $this->provider->all();
    }

    // ------------------------------------------------------------
    // 🧩 Internal
    // ------------------------------------------------------------

    public function getProvider() : SessionProvider
    {
        return $this->provider;
    }
}
