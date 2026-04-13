<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics;

/**
 * Decorates audit events with a request correlation id when flows do not set one.
 */
final readonly class CorrelatingAuditLog implements AuditLogInterface
{
    public function __construct(
        private AuditLogInterface $inner,
        private string $correlationId
    ) {}

    public function record(AuditEvent $event) : void
    {
        if ($event->correlationId !== null && trim($event->correlationId) !== '') {
            $this->inner->record($event);

            return;
        }

        $this->inner->record($event->withCorrelationId($this->correlationId));
    }
}
