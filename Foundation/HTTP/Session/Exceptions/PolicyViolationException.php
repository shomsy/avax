<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

/**
 * PolicyViolationException
 *
 * Thrown when a session policy is violated.
 *
 * Use Cases:
 * - MaxIdlePolicy timeout
 * - MaxLifetimePolicy exceeded
 * - SecureOnlyPolicy HTTPS requirement
 * - CrossAgentPolicy user agent mismatch
 *
 * @example
 *   try {
 *       $session->put('key', 'value');
 *   } catch (PolicyViolationException $e) {
 *       // Handle policy violation
 *   }
 *
 * @package Avax\HTTP\Session\Exceptions
 */
final class PolicyViolationException extends SessionException
{
    /**
     * Create exception for policy violation.
     *
     * @param string $policyName The policy that was violated.
     * @param string $reason     The violation reason.
     *
     * @return self
     */
    public static function forPolicy(string $policyName, string $reason) : self
    {
        return new self(
            sprintf('Policy violation [%s]: %s', $policyName, $reason)
        );
    }
}
