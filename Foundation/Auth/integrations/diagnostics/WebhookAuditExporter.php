<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flows\Diagnostics\AuditExporterInterface;

/**
 * Delivers normalized audit batches to an external webhook adapter.
 */
final readonly class WebhookAuditExporter implements AuditExporterInterface
{
    private NormalizeAuditEvent       $normalizeAuditEvent;
    private SendAuditWebhookInterface $sender;

    public function __construct(
        SendAuditWebhookInterface $sender,
        NormalizeAuditEvent       $normalizeAuditEvent = new NormalizeAuditEvent()
    )
    {
        $this->sender              = $sender;
        $this->normalizeAuditEvent = $normalizeAuditEvent;
    }

    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $this->sender->send(payload: $this->normalizeAuditEvent->execute(event: $event));
        }
    }
}
