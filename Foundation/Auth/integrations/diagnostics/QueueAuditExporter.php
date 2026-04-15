<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;

/**
 * Publishes normalized audit events into a queue adapter.
 */
final readonly class QueueAuditExporter implements AuditExporterInterface
{
    private NormalizeAuditEvent          $normalizeAuditEvent;
    private string                       $topic;
    private PublishAuditMessageInterface $publisher;

    public function __construct(
        PublishAuditMessageInterface $publisher,
        string|null                  $topic = null,
        NormalizeAuditEvent          $normalizeAuditEvent = new NormalizeAuditEvent()
    )
    {
        $topic                     ??= 'auth.audit';
        $this->publisher           = $publisher;
        $this->topic               = $topic;
        $this->normalizeAuditEvent = $normalizeAuditEvent;
    }

    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $this->publisher->publish(topic: $this->topic, message: $this->normalizeAuditEvent->execute(event: $event));
        }
    }
}
