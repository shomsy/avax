<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

use RuntimeException;

/**
 * PolicyGroupBuilder - Fluent Builder for Policy Groups
 *
 * Provides Spring Security-style fluent API for building policy groups.
 * Makes complex policy configurations readable and maintainable.
 *
 * @example Basic usage
 *   $policies = PolicyGroupBuilder::create()
 *       ->requireAll()
 *           ->add(new MaxIdlePolicy(900))
 *           ->add(new SecureOnlyPolicy())
 *       ->build();
 *
 * @example Complex groups
 *   $policies = PolicyGroupBuilder::create()
 *       ->requireAll()
 *           ->maxIdle(900)
 *           ->secureOnly()
 *           ->requireAny()
 *               ->ipBinding()
 *               ->userAgentBinding()
 *           ->endGroup()
 *       ->build();
 *
 * @package Avax\HTTP\Session\Shared\Security\Policies
 */
final class PolicyGroupBuilder
{
    /**
     * @var CompositePolicy|null Root composite policy
     */
    private CompositePolicy|null $root = null;

    /**
     * @var CompositePolicy|null Current working composite
     */
    private CompositePolicy|null $current = null;

    /**
     * @var array<CompositePolicy> Stack for nested groups
     */
    private array $stack = [];

    /**
     * PolicyGroupBuilder Constructor.
     */
    private function __construct()
    {
        // Use create() factory instead
    }

    /**
     * Create a security hardened preset.
     *
     * Includes:
     * - MaxIdle: 15 minutes
     * - MaxLifetime: 8 hours
     * - SecureOnly: HTTPS required
     * - IP Binding: Strict
     * - User Agent Binding
     *
     * @return PolicyInterface Built policy.
     */
    public static function securityHardened() : PolicyInterface
    {
        return self::create()
            ->requireAll(name: 'security_hardened')
            ->maxIdle(seconds: 900)              // 15 minutes
            ->maxLifetime(seconds: 28800)        // 8 hours
            ->secureOnly()
            ->ipBinding()
            ->userAgentBinding()
            ->build();
    }

    /**
     * Build the final policy structure.
     *
     * @return PolicyInterface|CompositePolicy Built policy.
     */
    public function build() : PolicyInterface|CompositePolicy
    {
        if ($this->root === null) {
            throw new RuntimeException(message: 'No policies configured. Use requireAll(), requireAny(), or requireNone() to start.');
        }

        // If only one policy in root, return it directly
        if ($this->root->count() === 1) {
            return $this->root->getPolicies()[0];
        }

        return $this->root;
    }

    /**
     * Add CrossAgentPolicy to current group.
     *
     * @return self Fluent interface.
     */
    public function userAgentBinding() : self
    {
        return $this->add(policy: new CrossAgentPolicy());
    }

    /**
     * Add a custom policy to current group.
     *
     * @param PolicyInterface $policy Policy to add.
     *
     * @return self Fluent interface.
     */
    public function add(PolicyInterface $policy) : self
    {
        if ($this->current === null) {
            throw new RuntimeException(message: 'No active group. Call requireAll(), requireAny(), or requireNone() first.');
        }

        $this->current->add(policy: $policy);

        return $this;
    }

    /**
     * Add SessionIpPolicy to current group.
     *
     * @param bool $strict Strict mode (default: true).
     *
     * @return self Fluent interface.
     */
    public function ipBinding(bool $strict = true) : self
    {
        return $this->add(policy: new SessionIpPolicy(strictMode: $strict));
    }

    /**
     * Add SecureOnlyPolicy to current group.
     *
     * @return self Fluent interface.
     */
    public function secureOnly() : self
    {
        return $this->add(policy: new SecureOnlyPolicy());
    }

    /**
     * Add MaxLifetimePolicy to current group.
     *
     * @param int $seconds Maximum lifetime in seconds.
     *
     * @return self Fluent interface.
     */
    public function maxLifetime(int $seconds) : self
    {
        return $this->add(policy: new MaxLifetimePolicy(maxLifetimeSeconds: $seconds));
    }

    /**
     * Add MaxIdlePolicy to current group.
     *
     * @param int $seconds Maximum idle time in seconds.
     *
     * @return self Fluent interface.
     */
    public function maxIdle(int $seconds) : self
    {
        return $this->add(policy: new MaxIdlePolicy(maxIdleSeconds: $seconds));
    }

    /**
     * Start a "require all" group (AND logic).
     *
     * All policies in this group must pass.
     *
     * @param string $name Group name.
     *
     * @return self Fluent interface.
     */
    public function requireAll(string $name = 'require_all') : self
    {
        return $this->startGroup(mode: CompositePolicy::MODE_ALL, name: $name);
    }

    /**
     * Start a new group.
     *
     * @param string $mode Group mode (all|any|none).
     * @param string $name Group name.
     *
     * @return self Fluent interface.
     */
    private function startGroup(string $mode, string $name) : self
    {
        $composite = new CompositePolicy(policies: [], mode: $mode, name: $name);

        if ($this->root === null) {
            // First group becomes root
            $this->root    = $composite;
            $this->current = $composite;
        } else {
            // Nested group
            if ($this->current === null) {
                throw new RuntimeException(message: 'Current group is null. This should not happen.');
            }

            // Add nested group to current
            $this->current->add(policy: $composite);

            // Push current to stack
            $this->stack[] = $this->current;

            // Make nested group current
            $this->current = $composite;
        }

        return $this;
    }

    /**
     * Create a new builder instance.
     *
     * @return self
     */
    public static function create() : self
    {
        return new self();
    }

    /**
     * Create a balanced security preset.
     *
     * Includes:
     * - MaxIdle: 30 minutes
     * - MaxLifetime: 24 hours
     * - SecureOnly: HTTPS required
     * - IP Binding: Relaxed
     *
     * @return PolicyInterface Built policy.
     */
    public static function balanced() : PolicyInterface
    {
        return self::create()
            ->requireAll(name: 'balanced')
            ->maxIdle(seconds: 1800)             // 30 minutes
            ->maxLifetime(seconds: 86400)        // 24 hours
            ->secureOnly()
            ->ipBinding(strict: false)
            ->build();
    }

    /**
     * Create a development-friendly preset.
     *
     * Includes:
     * - MaxIdle: 2 hours
     * - MaxLifetime: 7 days
     *
     * @return PolicyInterface Built policy.
     */
    public static function development() : PolicyInterface
    {
        return self::create()
            ->requireAll(name: 'development')
            ->maxIdle(seconds: 7200)             // 2 hours
            ->maxLifetime(seconds: 604800)       // 7 days
            ->build();
    }

    /**
     * Start a "require any" group (OR logic).
     *
     * At least one policy in this group must pass.
     *
     * @param string $name Group name.
     *
     * @return self Fluent interface.
     */
    public function requireAny(string $name = 'require_any') : self
    {
        return $this->startGroup(mode: CompositePolicy::MODE_ANY, name: $name);
    }

    /**
     * Start a "require none" group (inverse logic).
     *
     * All policies in this group must fail.
     *
     * @param string $name Group name.
     *
     * @return self Fluent interface.
     */
    public function requireNone(string $name = 'require_none') : self
    {
        return $this->startGroup(mode: CompositePolicy::MODE_NONE, name: $name);
    }

    /**
     * End current group and return to parent.
     *
     * @return self Fluent interface.
     */
    public function endGroup() : self
    {
        if (empty($this->stack)) {
            throw new RuntimeException(message: 'No group to end. Already at root level.');
        }

        $this->current = array_pop(array: $this->stack);

        return $this;
    }

    /**
     * Build and return as array of policies.
     *
     * Useful for bulk registration with PolicyEnforcer.
     *
     * @return array<PolicyInterface> Policies.
     */
    public function buildAsArray() : array
    {
        $policy = $this->build();

        if ($policy instanceof CompositePolicy) {
            return [$policy];
        }

        return [$policy];
    }
}
