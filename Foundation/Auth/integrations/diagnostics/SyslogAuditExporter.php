<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;

/**
 * Sends normalized audit events to a syslog-compatible adapter.
 */
final readonly class SyslogAuditExporter implements AuditExporterInterface
{
    private NormalizeAuditEvent        $normalizeAuditEvent;
    private SendSyslogMessageInterface $sender;

    public function __construct(
        SendSyslogMessageInterface $sender,
        NormalizeAuditEvent        $normalizeAuditEvent = new NormalizeAuditEvent()
    )
    {
        $this->sender              = $sender;
        $this->normalizeAuditEvent = $normalizeAuditEvent;
    }

    /**
     * @throws \JsonException
     */
    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $payload = $this->normalizeAuditEvent->execute(event: $event);
            $this->sender->send(severity: 'info', message: json_encode($payload, JSON_THROW_ON_ERROR));
        }
    }
}
