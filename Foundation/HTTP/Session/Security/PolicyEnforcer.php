<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Security\Policies\PolicyInterface;
use Exception;

/**
 * PolicyEnforcer - Policy Enforcement Service
 *
 * Centralized service for enforcing session security policies.
 * Reduces SessionProvider complexity.
 *
 * @package Avax\HTTP\Session\Security
 */
final class PolicyEnforcer
{
    /**
     * @var array<PolicyInterface> Registered policies
     */
    private array $policies = [];

    /**
     * @var ?\Avax\HTTP\Session\Features\Audit Audit logger
     */
    private $audit = null;

    /**
     * PolicyEnforcer Constructor.
     *
     * @param \Avax\HTTP\Session\Features\Audit|null $audit Optional audit logger.
     */
    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    /**
     * Register multiple policies at once.
     *
     * @param array<PolicyInterface> $policies Policies to register.
     *
     * @return void
     */
    public function registerMany(array $policies) : void
    {
        foreach ($policies as $policy) {
            $this->register($policy);
        }
    }

    /**
     * Register a policy.
     *
     * @param PolicyInterface $policy The policy.
     *
     * @return void
     */
    public function register(PolicyInterface $policy) : void
    {
        $this->policies[] = $policy;
    }

    /**
     * Enforce all registered policies.
     *
     * OWASP ASVS 3.4.2 - Security event audit logging
     *
     * @param array<string, mixed> $data Session data for policy checks.
     *
     * @return void
     * @throws \RuntimeException If any policy is violated.
     */
    public function enforce(array $data) : void
    {
        foreach ($this->policies as $policy) {
            try {
                $policy->enforce($data);
            } catch (Exception $e) {
                // AUDIT: Log security violation
                if ($this->audit !== null) {
                    $this->audit->record('policy_violation', [
                        'policy' => $policy->getName(),
                        'reason' => $e->getMessage()
                    ]);
                }

                // Re-throw exception
                throw $e;
            }
        }
    }

    /**
     * Get all registered policies.
     *
     * @return array<PolicyInterface> Policies.
     */
    public function getPolicies() : array
    {
        return $this->policies;
    }
}
