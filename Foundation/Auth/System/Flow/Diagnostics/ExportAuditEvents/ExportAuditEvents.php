<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics\ExportAuditEvents;

use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;
use Avax\Auth\System\Flow\Diagnostics\DrainAuditLogInterface;

final readonly class ExportAuditEvents
{
    private AuditExporterInterface $exporter;
    private DrainAuditLogInterface $auditLog;

    public function __construct(
        DrainAuditLogInterface $auditLog,
        AuditExporterInterface $exporter
    )
    {
        $this->auditLog = $auditLog;
        $this->exporter = $exporter;
    }

    public function execute() : int
    {
        $events = $this->auditLog->drain();

        if ($events === []) {
            return 0;
        }

        $this->exporter->export(events: $events);

        return count($events);
    }
}
