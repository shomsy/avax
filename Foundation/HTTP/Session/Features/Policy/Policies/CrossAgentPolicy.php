<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy\Policies;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Policy\SessionPolicyInterface;

/**
 * CrossAgentPolicy
 *
 * Prevents session hijacking by validating user agent.
 *
 * @package Avax\HTTP\Session\Features\Policy\Policies
 */
final readonly class CrossAgentPolicy implements SessionPolicyInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(SessionContext $context): bool
    {
        $storedAgent = $context->custom['user_agent'] ?? null;
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($storedAgent === null) {
            return true; // First request, no agent stored yet
        }

        return $storedAgent === $currentAgent;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return 'Session user agent mismatch detected (possible hijacking attempt)';
    }
}
