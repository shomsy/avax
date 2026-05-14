<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\ExportAuditEvents;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditExporterInterface;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\DrainAuditLogInterface;

final readonly class ExportAuditEvents
{
    public function __construct(private DrainAuditLogInterface $drainAuditLog, private AuditExporterInterface $auditExporter) {}

    public function execute() : int
    {
        $events = $this->drainAuditLog->drain();

        if ($events === []) {
            return 0;
        }

        $this->auditExporter->export(events: $events);

        return count(value: $events);
    }
}
