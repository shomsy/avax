<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit;

/**
 * Decorates audit events with a request correlation id when flows do not set one.
 */
final readonly class CorrelatingAuditLog implements AuditLogInterface
{
    public function __construct(private AuditLogInterface $inner, private string $correlationId) {}

    public function record(AuditEvent $event): void
    {
        if ($event->correlationId !== null && trim(string: $event->correlationId) !== '') {
            $this->inner->record(event: $event);

            return;
        }

        $this->inner->record(event: $event->withCorrelationId(correlationId: $this->correlationId));
    }
}
