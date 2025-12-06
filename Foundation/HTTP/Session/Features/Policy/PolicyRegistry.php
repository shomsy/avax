<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy;

/**
 * PolicyRegistry
 *
 * Central registry for all active session policies.
 *
 * @package Avax\HTTP\Session\Features\Policy
 */
final class PolicyRegistry
{
    /**
     * Registered policies.
     *
     * @var array<string, SessionPolicyInterface>
     */
    private array $policies = [];

    /**
     * Register a policy.
     *
     * @param string                  $name   Policy name.
     * @param SessionPolicyInterface $policy The policy instance.
     *
     * @return void
     */
    public function register(string $name, SessionPolicyInterface $policy): void
    {
        $this->policies[$name] = $policy;
    }

    /**
     * Get policy by name.
     *
     * @param string $name The policy name.
     *
     * @return SessionPolicyInterface|null
     */
    public function get(string $name): SessionPolicyInterface|null
    {
        return $this->policies[$name] ?? null;
    }

    /**
     * Check if policy exists.
     *
     * @param string $name The policy name.
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->policies[$name]);
    }

    /**
     * Get all policies.
     *
     * @return array<string, SessionPolicyInterface>
     */
    public function all(): array
    {
        return $this->policies;
    }

    /**
     * Remove a policy.
     *
     * @param string $name The policy name.
     *
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->policies[$name]);
    }
}
