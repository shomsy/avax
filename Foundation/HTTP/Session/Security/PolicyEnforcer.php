<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Features\Audit;
use Avax\HTTP\Session\Security\Policies\PolicyInterface;
use Exception;

/**
 * 🧠 PolicyEnforcer - Centralized Session Policy Enforcement
 * ==========================================================
 *
 * Coordinates and executes all registered session security policies.
 * Acts as a mediator between SessionProvider and PolicyGroupBuilder.
 *
 * ✅ Enforces compliance with OWASP ASVS 3.4.2 (Audit logging of violations)
 * ✅ Integrates seamlessly with the Audit feature for traceability
 * ✅ Keeps SessionProvider lightweight and clean
 *
 * @package Avax\HTTP\Session\Security
 */
final class PolicyEnforcer
{
    /**
     * @var array<PolicyInterface>
     */
    private array $policies = [];

    /**
     * @var Audit|null
     */
    private Audit|null $audit;

    /**
     * @param Audit|null $audit Optional PSR-3 compatible audit logger.
     */
    public function __construct(Audit|null $audit = null)
    {
        $this->audit = $audit;
    }

    /**
     * Register multiple policies at once.
     *
     * @param array<PolicyInterface> $policies
     */
    public function registerMany(array $policies) : void
    {
        foreach ($policies as $policy) {
            $this->register($policy);
        }
    }

    /**
     * Register a single policy.
     */
    public function register(PolicyInterface $policy) : void
    {
        $this->policies[] = $policy;
    }

    /**
     * Enforce all registered security policies.
     *
     * @param array<string, mixed> $data Contextual session data.
     *
     * @throws \RuntimeException If any policy is violated.
     * @throws \Exception
     */
    public function enforce(array $data) : void
    {
        foreach ($this->policies as $policy) {
            try {
                $policy->enforce($data);
            } catch (Exception $e) {
                if ($this->audit !== null) {
                    $this->audit->record(event: 'policy_violation', data: [
                        'policy'  => $policy->getName(),
                        'reason'  => $e->getMessage(),
                        'user_id' => $data['user_id'] ?? null,
                        'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
                        'time'    => date('c'),
                    ]);
                }

                throw $e;
            }
        }
    }

    /**
     * @return array<PolicyInterface>
     */
    public function getPolicies() : array
    {
        return $this->policies;
    }
}
