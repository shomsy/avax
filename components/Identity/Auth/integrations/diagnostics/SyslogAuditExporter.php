<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditExporterInterface;
use JsonException;

/**
 * Sends normalized audit events to a syslog-compatible adapter.
 */
final readonly class SyslogAuditExporter implements AuditExporterInterface
{
    public function __construct(private SendSyslogMessageInterface $sender, private NormalizeAuditEvent $normalizeAuditEvent = new NormalizeAuditEvent()) {}

    /**
     * @throws JsonException
     */
    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $payload = $this->normalizeAuditEvent->execute(event: $event);
            $this->sender->send(severity: 'info', message: json_encode(value: $payload, flags: JSON_THROW_ON_ERROR));
        }
    }
}
