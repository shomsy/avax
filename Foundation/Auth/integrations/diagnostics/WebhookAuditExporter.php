<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;

/**
 * Delivers normalized audit batches to an external webhook adapter.
 */
final readonly class WebhookAuditExporter implements AuditExporterInterface
{
    public function __construct(
        private SendAuditWebhookInterface $sender,
        private NormalizeAuditEvent $normalizeAuditEvent = new NormalizeAuditEvent()
    ) {}

    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $this->sender->send($this->normalizeAuditEvent->execute($event));
        }
    }
}
