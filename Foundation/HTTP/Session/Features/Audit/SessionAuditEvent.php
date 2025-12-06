<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Audit;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionAuditEvent
 *
 * Audit event for session operations.
 *
 * @package Avax\HTTP\Session\Features\Audit
 */
final readonly class SessionAuditEvent extends SessionEvent
{
    /**
     * Create audit event.
     *
     * @param string               $action  The action performed.
     * @param array<string, mixed> $context Event context.
     *
     * @return self
     */
    public static function create(string $action, array $context): self
    {
        return new self(
            action: $action,
            context: array_merge($context, ['audit' => true]),
            occurredAt: new DateTimeImmutable()
        );
    }
}
