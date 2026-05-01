<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit;

/**
 * Decorates audit events with a request correlation id when flows do not set one.
 */
final readonly class CorrelatingAuditLog implements AuditLogInterface
{
    public function __construct(private AuditLogInterface $auditLog, private string $correlationId) {}

    public function record(AuditEvent $auditEvent) : void
    {
        if ($auditEvent->correlationId !== null && trim(string: $auditEvent->correlationId) !== '') {
            $this->auditLog->record(event: $auditEvent);

            return;
        }

        $this->auditLog->record(event: $auditEvent->withCorrelationId(correlationId: $this->correlationId));
    }
}
