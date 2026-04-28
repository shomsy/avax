<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\ExportAuditEvents;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditExporterInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\DrainAuditLogInterface;

final readonly class ExportAuditEvents
{
    public function __construct(private DrainAuditLogInterface $auditLog, private AuditExporterInterface $exporter) {}

    public function execute() : int
    {
        $events = $this->auditLog->drain();

        if ($events === []) {
            return 0;
        }

        $this->exporter->export(events: $events);

        return count(value: $events);
    }
}
