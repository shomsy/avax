<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * PolicyViolatedEvent
 *
 * Dispatched when a policy is violated.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class PolicyViolatedEvent extends SessionEvent
{
    /**
     * Create new PolicyViolatedEvent.
     *
     * @param string $policyName Policy that was violated.
     * @param string $message    Violation message.
     *
     * @return self
     */
    public static function create(string $policyName, string $message): self
    {
        return new self(
            action: 'PolicyViolated',
            context: [
                'policy' => $policyName,
                'message' => $message,
                'security_event' => true,
            ],
            occurredAt: new DateTimeImmutable()
        );
    }
}
