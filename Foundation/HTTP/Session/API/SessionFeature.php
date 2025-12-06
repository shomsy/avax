<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\API;

use Avax\HTTP\Session\Core\SessionKernel;
use Avax\HTTP\Session\Features\Crypto\CryptoFeatureKernel;
use Avax\HTTP\Session\Features\Crypto\CryptoInterface;
use Avax\HTTP\Session\Features\Events\SessionEventBus;
use Avax\HTTP\Session\Features\Policy\SessionPolicyEnforcer;
use Avax\HTTP\Session\Features\TTL\TTLManagerInterface;
use Avax\HTTP\Session\Storage\SessionStore;
use Avax\Security\Encryption\Contracts\EncrypterInterface;

/**
 * SessionFeature
 *
 * Main DSL entry point for Session subsystem.
 *
 * This is the "language kernel" that provides fluent API
 * for configuring and starting sessions.
 *
 * Usage:
 *   $session = SessionFeature::use()
 *       ->driver('redis')
 *       ->crypto($crypto)
 *       ->ttl(3600)
 *       ->start();
 *
 * @package Avax\HTTP\Session\API
 */
final class SessionFeature
{
    private SessionStore|null $store = null;
    private CryptoInterface|null $crypto = null;
    private TTLManagerInterface|null $ttlManager = null;
    private SessionEventBus|null $eventBus = null;
    private SessionPolicyEnforcer|null $policyEnforcer = null;
    private int|null $ttl = null;

    /**
     * Create new SessionFeature builder.
     *
     * @return self
     */
    public static function use(): self
    {
        return new self();
    }

    /**
     * Set storage driver.
     *
     * @param string|SessionStore $driver The driver name or instance.
     *
     * @return self
     */
    public function driver(string|SessionStore $driver): self
    {
        if ($driver instanceof SessionStore) {
            $this->store = $driver;
        } else {
            // Resolve driver from container
            $this->store = app("session.driver.{$driver}");
        }

        return $this;
    }

    /**
     * Set crypto adapter.
     *
     * @param CryptoInterface $crypto The crypto adapter.
     *
     * @return self
     */
    public function crypto(CryptoInterface $crypto): self
    {
        $this->crypto = $crypto;
        return $this;
    }

    /**
     * Set TTL manager.
     *
     * @param TTLManagerInterface $manager The TTL manager.
     *
     * @return self
     */
    public function ttlManager(TTLManagerInterface $manager): self
    {
        $this->ttlManager = $manager;
        return $this;
    }

    /**
     * Set default TTL.
     *
     * @param int $seconds TTL in seconds.
     *
     * @return self
     */
    public function ttl(int $seconds): self
    {
        $this->ttl = $seconds;
        return $this;
    }

    /**
     * Set event bus.
     *
     * @param SessionEventBus $eventBus The event bus.
     *
     * @return self
     */
    public function events(SessionEventBus $eventBus): self
    {
        $this->eventBus = $eventBus;
        return $this;
    }

    /**
     * Configure policy enforcer.
     *
     * @param callable $callback Configuration callback.
     *
     * @return self
     */
    public function policy(callable $callback): self
    {
        $this->policyEnforcer = new SessionPolicyEnforcer();
        $callback($this->policyEnforcer);
        return $this;
    }

    /**
     * Start and return SessionManager.
     *
     * @return SessionManager
     */
    public function start(): SessionManager
    {
        // Boot kernel
        $kernel = new SessionKernel();
        $kernel->register(new CryptoFeatureKernel());
        $kernel->boot();

        // Create SessionManager with configured dependencies
        return new SessionManager(
            store: $this->store ?? app(SessionStore::class),
            flash: app('session.flash'),
            encrypter: app(EncrypterInterface::class),
            eventBus: $this->eventBus ?? app(SessionEventBus::class),
            policyEnforcer: $this->policyEnforcer
        );
    }

    /**
     * Boot with feature kernels.
     *
     * @param array<FeatureKernelInterface> $features Feature kernels to register.
     *
     * @return SessionKernel
     */
    public static function boot(array $features = []): SessionKernel
    {
        $kernel = new SessionKernel();

        foreach ($features as $feature) {
            $kernel->register($feature);
        }

        $kernel->boot();

        return $kernel;
    }
}
