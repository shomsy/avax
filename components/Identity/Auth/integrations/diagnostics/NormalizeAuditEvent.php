<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;

/**
 * Builds stable export payloads for audit adapters.
 */
final readonly class NormalizeAuditEvent
{
    private bool $maskSensitiveContext;
    private MaskAuditContext $maskAuditContext;

    public function __construct(
        MaskAuditContext                           $maskAuditContext = null,
        bool                                       $maskSensitiveContext = null,
        private AuditLegalHoldPolicyInterface|null $legalHoldPolicy = null,
    )
    {
        $maskAuditContext     ??= new MaskAuditContext();
        $maskSensitiveContext ??= true;
        $this->maskAuditContext     = $maskAuditContext;
        $this->maskSensitiveContext = $maskSensitiveContext;
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(AuditEvent $event) : array
    {
        $maskSensitiveContext = $this->maskSensitiveContext
            && ! ($this->legalHoldPolicy?->preserveSensitiveContext(event: $event) ?? false);
        $context = $maskSensitiveContext
            ? $this->maskAuditContext->execute(context: $event->context)
            : $event->context;

        return [
            'name'           => $event->name,
            'occurred_at'    => $event->occurredAt->format(format: DATE_ATOM),
            'correlation_id' => $event->correlationId,
            'context'        => $context,
        ];
    }
}
