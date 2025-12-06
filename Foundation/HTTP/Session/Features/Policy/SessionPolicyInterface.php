<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy;

use Avax\HTTP\Session\Core\SessionContext;

/**
 * SessionPolicyInterface
 *
 * Contract for session security and compliance policies.
 *
 * Policies validate session behavior and enforce rules like
 * max idle time, max lifetime, secure transport requirements, etc.
 *
 * Enterprise Rules:
 * - Validation: Each policy validates one aspect.
 * - Composability: Multiple policies can be combined.
 * - Clear messaging: Violations have descriptive messages.
 *
 * @package Avax\HTTP\Session\Features\Policy
 */
interface SessionPolicyInterface
{
    /**
     * Validate the session context against this policy.
     *
     * @param SessionContext $context The session context to validate.
     *
     * @return bool True if valid, false if policy is violated.
     */
    public function validate(SessionContext $context): bool;

    /**
     * Get the policy violation message.
     *
     * @return string Descriptive message explaining the policy.
     */
    public function getMessage(): string;
}
