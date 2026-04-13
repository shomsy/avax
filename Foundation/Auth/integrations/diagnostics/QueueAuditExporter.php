<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;

/**
 * Publishes normalized audit events into a queue adapter.
 */
final readonly class QueueAuditExporter implements AuditExporterInterface
{
    public function __construct(
        private PublishAuditMessageInterface $publisher,
        private string $topic = 'auth.audit',
        private NormalizeAuditEvent $normalizeAuditEvent = new NormalizeAuditEvent()
    ) {}

    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $this->publisher->publish(topic: $this->topic, message: $this->normalizeAuditEvent->execute(event: $event));
        }
    }
}
