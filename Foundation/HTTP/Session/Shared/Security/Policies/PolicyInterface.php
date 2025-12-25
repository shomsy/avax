<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

/**
 * PolicyInterface - Session Security Policy Contract
 *
 * Defines the contract for session security policies.
 * Policies are enforced before each session operation.
 *
 * @package Avax\HTTP\Session\Policies
 */
interface PolicyInterface
{
    /**
     * Enforce the policy rules.
     *
     * @param array<string, mixed> $data Current session data.
     *
     * @return void
     * @throws \RuntimeException If policy is violated.
     */
    public function enforce(array $data) : void;

    /**
     * Get the policy name.
     *
     * @return string The policy identifier.
     */
    public function getName() : string;
}
