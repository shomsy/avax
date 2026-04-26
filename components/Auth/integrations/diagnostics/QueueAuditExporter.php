<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditExporterInterface;

/**
 * Publishes normalized audit events into a queue adapter.
 */
final readonly class QueueAuditExporter implements AuditExporterInterface
{
    private string $topic;

    public function __construct(
        private PublishAuditMessageInterface $publisher,
        string|null                          $topic = null,
        private NormalizeAuditEvent          $normalizeAuditEvent = new NormalizeAuditEvent()
    )
    {
        $topic       ??= 'auth.audit';
        $this->topic = $topic;
    }

    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $this->publisher->publish(topic: $this->topic, message: $this->normalizeAuditEvent->execute(event: $event));
        }
    }
}
