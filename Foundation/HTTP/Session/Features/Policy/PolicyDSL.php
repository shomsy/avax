<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy;

use Avax\HTTP\Session\Features\Policy\Policies\CrossAgentPolicy;
use Avax\HTTP\Session\Features\Policy\Policies\MaxIdlePolicy;
use Avax\HTTP\Session\Features\Policy\Policies\MaxLifetimePolicy;
use Avax\HTTP\Session\Features\Policy\Policies\SecureOnlyPolicy;

/**
 * PolicyDSL
 *
 * Fluent DSL for configuring session policies.
 *
 * Usage:
 *   $policy = PolicyDSL::create()
 *       ->maxIdle(900)
 *       ->maxLifetime(3600)
 *       ->requireSecureTransport()
 *       ->disallowCrossAgent();
 *
 * @package Avax\HTTP\Session\Features\Policy
 */
final class PolicyDSL
{
    private SessionPolicyEnforcer $enforcer;

    /**
     * PolicyDSL Constructor.
     */
    private function __construct()
    {
        $this->enforcer = new SessionPolicyEnforcer();
    }

    /**
     * Create new PolicyDSL instance.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set max idle time policy.
     *
     * @param int $seconds Max idle time in seconds.
     *
     * @return self
     */
    public function maxIdle(int $seconds): self
    {
        $this->enforcer->addPolicy(new MaxIdlePolicy($seconds));
        return $this;
    }

    /**
     * Set max lifetime policy.
     *
     * @param int $seconds Max lifetime in seconds.
     *
     * @return self
     */
    public function maxLifetime(int $seconds): self
    {
        $this->enforcer->addPolicy(new MaxLifetimePolicy($seconds));
        return $this;
    }

    /**
     * Require secure transport.
     *
     * @return self
     */
    public function requireSecureTransport(): self
    {
        $this->enforcer->addPolicy(new SecureOnlyPolicy());
        return $this;
    }

    /**
     * Disallow cross-agent sessions.
     *
     * @return self
     */
    public function disallowCrossAgent(): self
    {
        $this->enforcer->addPolicy(new CrossAgentPolicy());
        return $this;
    }

    /**
     * Get configured enforcer.
     *
     * @return SessionPolicyEnforcer
     */
    public function getEnforcer(): SessionPolicyEnforcer
    {
        return $this->enforcer;
    }
}
