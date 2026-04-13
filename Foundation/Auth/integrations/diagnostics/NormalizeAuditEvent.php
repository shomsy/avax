<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditEvent;

/**
 * Builds stable export payloads for audit adapters.
 */
final readonly class NormalizeAuditEvent
{
    public function __construct(
        private MaskAuditContext $maskAuditContext = new MaskAuditContext(),
        private bool $maskSensitiveContext = true,
        private AuditLegalHoldPolicyInterface|null $legalHoldPolicy = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(AuditEvent $event) : array
    {
        $maskSensitiveContext = $this->maskSensitiveContext
            && ! ($this->legalHoldPolicy?->preserveSensitiveContext($event) ?? false);
        $context = $maskSensitiveContext
            ? $this->maskAuditContext->execute($event->context)
            : $event->context;

        return [
            'name' => $event->name,
            'occurred_at' => $event->occurredAt->format(DATE_ATOM),
            'correlation_id' => $event->correlationId,
            'context' => $context,
        ];
    }
}
