<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics;

/**
 * Decorates audit events with a request correlation id when flows do not set one.
 */
final readonly class CorrelatingAuditLog implements AuditLogInterface
{
    private string            $correlationId;
    private AuditLogInterface $inner;

    public function __construct(
        AuditLogInterface $inner,
        string            $correlationId
    )
    {
        $this->inner         = $inner;
        $this->correlationId = $correlationId;
    }

    public function record(AuditEvent $event) : void
    {
        if ($event->correlationId !== null && trim($event->correlationId) !== '') {
            $this->inner->record(event: $event);

            return;
        }

        $this->inner->record(event: $event->withCorrelationId(correlationId: $this->correlationId));
    }
}
