<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * CompositePolicy - Composite Policy Pattern
 *
 * Combines multiple policies into a single policy.
 * Enables grouping and reusable policy sets.
 *
 * Execution Modes:
 * - ALL: All policies must pass (default, AND logic)
 * - ANY: At least one policy must pass (OR logic)
 * - NONE: All policies must fail (inverse logic)
 *
 * @example
 *   $composite = new CompositePolicy([
 *       new MaxIdlePolicy(900),
 *       new SecureOnlyPolicy(),
 *       new SessionIpPolicy()
 *   ]);
 *
 * @example With ANY mode
 *   $composite = CompositePolicy::any([
 *       new AdminRolePolicy(),
 *       new SuperuserPolicy()
 *   ]);
 *
 * @package Avax\HTTP\Session\Shared\Security\Policies
 */
final class CompositePolicy implements PolicyInterface
{
    public const MODE_ALL  = 'all';
    public const MODE_ANY  = 'any';
    public const MODE_NONE = 'none';

    /**
     * @var array<PolicyInterface> Child policies
     */
    private array $policies = [];

    /**
     * CompositePolicy Constructor.
     *
     * @param array<PolicyInterface> $policies Child policies.
     * @param string|null            $mode     Execution mode (all|any|none).
     * @param string                 $name     Policy name.
     */
    public function __construct(
        array|null              $policies = null,
        private string|null     $mode = null,
        private readonly string $name = 'composite'
    )
    {
        $policies   ??= [];
        $this->mode ??= self::MODE_ALL;
        foreach ($policies as $policy) {
            $this->add(policy: $policy);
        }
    }

    /**
     * Add a child policy.
     *
     * @param PolicyInterface $policy Policy to add.
     *
     * @return self Fluent interface.
     */
    public function add(PolicyInterface $policy) : self
    {
        $this->policies[] = $policy;

        return $this;
    }

    /**
     * Create composite with ALL mode (AND logic).
     *
     * All policies must pass.
     *
     * @param array<PolicyInterface> $policies Child policies.
     * @param string                 $name     Policy name.
     *
     * @return self
     */
    public static function all(array $policies, string $name = 'composite_all') : self
    {
        return new self(policies: $policies, mode: self::MODE_ALL, name: $name);
    }

    /**
     * Create composite with ANY mode (OR logic).
     *
     * At least one policy must pass.
     *
     * @param array<PolicyInterface> $policies Child policies.
     * @param string                 $name     Policy name.
     *
     * @return self
     */
    public static function any(array $policies, string $name = 'composite_any') : self
    {
        return new self(policies: $policies, mode: self::MODE_ANY, name: $name);
    }

    /**
     * Create composite with NONE mode (inverse logic).
     *
     * All policies must fail.
     *
     * @param array<PolicyInterface> $policies Child policies.
     * @param string                 $name     Policy name.
     *
     * @return self
     */
    public static function none(array $policies, string $name = 'composite_none') : self
    {
        return new self(policies: $policies, mode: self::MODE_NONE, name: $name);
    }

    /**
     * {@inheritdoc}
     */
    public function enforce(array $data) : void
    {
        if (empty($this->policies)) {
            return; // No policies to enforce
        }

        match ($this->mode) {
            self::MODE_ALL  => $this->enforceAll(data: $data),
            self::MODE_ANY  => $this->enforceAny(data: $data),
            self::MODE_NONE => $this->enforceNone(data: $data),
            default         => throw new InvalidArgumentException(message: "Invalid mode: {$this->mode}")
        };
    }

    /**
     * Enforce ALL policies (AND logic).
     *
     * @param array<string, mixed> $data Session data.
     *
     * @return void
     * @throws \RuntimeException If any policy fails.
     */
    private function enforceAll(array $data) : void
    {
        $failures = [];

        foreach ($this->policies as $policy) {
            try {
                $policy->enforce(data: $data);
            } catch (Exception $e) {
                $failures[] = sprintf(
                    '%s: %s',
                    $policy->getName(),
                    $e->getMessage()
                );
            }
        }

        if (! empty($failures)) {
            throw new RuntimeException(
                message: sprintf(
                    'Composite policy "%s" failed (ALL mode): %s',
                    $this->name,
                    implode(separator: '; ', array: $failures)
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Enforce ANY policy (OR logic).
     *
     * @param array<string, mixed> $data Session data.
     *
     * @return void
     * @throws \RuntimeException If all policies fail.
     */
    private function enforceAny(array $data) : void
    {
        $failures = [];

        foreach ($this->policies as $policy) {
            try {
                $policy->enforce(data: $data);

                return; // At least one passed, success!
            } catch (Exception $e) {
                $failures[] = sprintf(
                    '%s: %s',
                    $policy->getName(),
                    $e->getMessage()
                );
            }
        }

        // All policies failed
        throw new RuntimeException(
            message: sprintf(
                'Composite policy "%s" failed (ANY mode): All child policies failed: %s',
                $this->name,
                implode(separator: '; ', array: $failures)
            )
        );
    }

    /**
     * Enforce NONE policy (inverse logic).
     *
     * @param array<string, mixed> $data Session data.
     *
     * @return void
     * @throws \RuntimeException If any policy passes.
     */
    private function enforceNone(array $data) : void
    {
        foreach ($this->policies as $policy) {
            try {
                $policy->enforce(data: $data);

                // Policy passed, but we wanted it to fail
                throw new RuntimeException(
                    message: sprintf(
                        'Composite policy "%s" failed (NONE mode): Policy "%s" should have failed but passed',
                        $this->name,
                        $policy->getName()
                    )
                );
            } catch (Exception $e) {
                // Policy failed, which is what we wanted (continue)
                continue;
            }
        }

        // All policies failed, which is what we wanted (success)
    }

    /**
     * Get child policies.
     *
     * @return array<PolicyInterface> Child policies.
     */
    public function getPolicies() : array
    {
        return $this->policies;
    }

    /**
     * Get execution mode.
     *
     * @return string Mode (all|any|none).
     */
    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * Check if composite is empty.
     *
     * @return bool True if no child policies.
     */
    public function isEmpty() : bool
    {
        return empty($this->policies);
    }

    /**
     * Get number of child policies.
     *
     * @return int Count.
     */
    public function count() : int
    {
        return count(value: $this->policies);
    }
}
